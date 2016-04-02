<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\job\import;

use core\models\Import;
use core\models\Language;
use core\models\User;
use core\models\Field;
use core\models\FieldValue as Fv;
use core\classes\Csv;

class FieldValue
{

    /**
     * Import model instance
     * @var \core\models\Import $import
     */
    protected $import;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * CSV class instance
     * @var \core\classes\Csv $csv
     */
    protected $csv;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Field value model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Constructor
     * @param Import $import
     * @param Language $language
     * @param User $user
     * @param Fv $field_value
     * @param Field $field
     * @param Csv $csv
     */
    public function __construct(Import $import, Language $language, User $user, Fv $field_value, Field $field, Csv $csv)
    {
        $this->import = $import;
        $this->language = $language;
        $this->user = $user;
        $this->csv = $csv;
        $this->field = $field;
        $this->field_value = $field_value;
    }

    /**
     * Processes one job iteration
     * @param array $job
     * @param string $operation_id
     * @param integer $done
     * @param array $context
     * @param array $options
     * @return array
     */
    public function process($job, $operation_id, $done, $context, $options)
    {
        $import_operation = $options['operation'];
        $header = $import_operation['csv']['header'];
        $limit = $options['limit'];
        $delimiter = $this->import->getCsvDelimiter();

        $this->csv->setFile($options['filepath'], $options['filesize'])
                ->setHeader($header)
                ->setLimit($limit)
                ->setDelimiter($delimiter);

        $offset = isset($context['offset']) ? $context['offset'] : 0;
        $line = isset($context['line']) ? $context['line'] : 2; // 2 - skip 0 and header

        if ($offset) {
            $this->csv->setOffset($offset);
        } else {
            $this->csv->skipHeader();
        }

        $rows = $this->csv->parse();

        if (!$rows) {
            return array('done' => $job['total']);
        }

        $position = $this->csv->getOffset();
        $result = $this->import($rows, $line, $options);
        $line += count($rows);
        $bytes = $position ? $position : $job['total'];

        $errors = $this->import->getErrors($result['errors'], $import_operation);

        return array(
            'done' => $bytes,
            'increment' => false,
            'inserted' => $result['inserted'],
            'updated' => $result['updated'],
            'errors' => $errors['count'],
            'context' => array('offset' => $position, 'line' => $line));
    }

    /**
     * Adds/updates from an array of rows
     * @param array $rows
     * @param integer $line
     * @param array $options
     * @return array
     */
    public function import($rows, $line, $options)
    {
        $inserted = 0;
        $updated = 0;
        $errors = array();
        $operation = $options['operation'];

        foreach ($rows as $index => $row) {
            $line += $index;
            $data = array_filter(array_map('trim', $row));
            $update = (isset($data['field_value_id']) && is_numeric($data['field_value_id']));

            if ($update && !$this->user->access('field_value_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('field_value_add')) {
                continue;
            }

            if (!$this->validateTitle($data, $errors, $line)) {
                continue;
            }

            if (!empty($options['unique']) && !$this->validateUnique($data, $errors, $line, $update)) {
                continue;
            }

            if (!$this->validateField($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateColor($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateImages($data, $errors, $line, $operation)) {
                continue;
            }

            if (isset($data['weight'])) {
                $data['weight'] = (int) $data['weight'];
            }

            if ($update) {
                $updated += $this->update($data['field_value_id'], $data);
                continue;
            }

            $inserted += $this->add($data, $errors, $line);
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Validates titles
     * @param array $data
     * @param array $errors
     * @return boolean
     */
    protected function validateTitle(&$data, &$errors, $line)
    {
        if (isset($data['title']) && mb_strlen($data['title']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Title must not be longer than 255 characters')));
            return false;
        }

        return true;
    }

    /**
     * Check if a fied value exists
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @param boolean $update
     * @return boolean
     */
    protected function validateUnique(&$data, &$errors, $line, $update)
    {
        if (!isset($data['title'])) {
            return true;
        }

        $unique = true;
        $existing = $this->getFieldValue($data['title']);
        if ($existing) {
            $unique = false;
        }

        if ($update && isset($existing['field_value_id']) && $existing['field_value_id'] == $data['field_value_id']) {
            $unique = true;
            $data['title'] = null;
        }

        if (!$unique) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Field value name already exists')));
            return false;
        }

        return true;
    }

    /**
     * Returns an array of field value data by ID or title
     * @param integer|string $field_value_id
     * @return array
     */
    protected function getFieldValue($field_value_id)
    {
        if (is_numeric($field_value_id)) {
            return $this->field_value->get($field_value_id);
        }

        $matches = array();
        foreach ($this->field_value->getList(array('title' => $field_value_id)) as $field_value) {
            if ($field_value['title'] === $field_value_id) {
                $matches[] = $field_value;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Checks if a field exists and unique
     * @param array $data
     * @param type $errors
     * @param type $line
     * @return boolean
     */
    protected function validateField(&$data, &$errors, $line)
    {
        if (!isset($data['field_id'])) {
            return true;
        }

        $field = $this->getField($data['field_id']);

        if (empty($field['field_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Field @id neither exists or unique', array(
                    '@id' => $data['field_id']))));
            return false;
        }

        $data['field_id'] = $field['field_id'];
        return true;
    }

    /**
     * Returns an array of field data by ID or title
     * @param type $field_id
     * @return type
     */
    protected function getField($field_id)
    {
        if (is_numeric($field_id)) {
            return $this->field->get($field_id);
        }

        $matches = array();
        foreach ($this->field->getList(array('title' => $field_id)) as $field) {
            if ($field['title'] === $field_id) {
                $matches[] = $field;
            }
        }

        return (count($matches) == 1) ? reset($matches) : $matches;
    }

    /**
     * Validates HEX color code
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateColor(&$data, &$errors, $line)
    {
        if (isset($data['color']) && !preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $data['color'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Color @code in not a valid HEX code', array('@code' => $data['color']))));
            return false;
        }

        return true;
    }

    /**
     * Downloads and/or validates images
     * @param array $data
     * @param array $errors
     * @param array $operation
     * @return boolean
     */
    protected function validateImages(&$data, &$errors, $line, $operation)
    {
        if (!isset($data['image'])) {
            return true;
        }

        $download = $this->import->getImages($data['image'], $operation);
        if ($download['errors']) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => implode(',', $download['errors'])));
        }

        $image = $download['images'] ? reset($download['images']) : array();

        if (isset($image['path'])) {
            $data['path'] = $image['path'];
        }

        return true;
    }

    /**
     * Updates a field value
     * @param integer $field_value_id
     * @param array $data
     * @return integer
     */
    protected function update($field_value_id, $data)
    {
        return (int) $this->field_value->update($field_value_id, $data);
    }

    /**
     * Adds a new field value
     * @param array $data
     * @param array $errors
     * @return integer
     */
    protected function add(&$data, &$errors, $line)
    {
        if (empty($data['title'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Empty field value title, skipped')));
            return 0;
        }

        if (empty($data['field_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Empty field, skipped')));
            return 0;
        }

        if (!isset($data['weight'])) {
            $data['weight'] = $line;
        }

        return $this->field_value->add($data) ? 1 : 0;
    }
}
