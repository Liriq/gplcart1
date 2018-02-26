<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;

/**
 * Handles incoming requests and outputs data related to product fields
 */
class Field extends Controller
{

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * Entity translation model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current field
     * @var array
     */
    protected $data_field = array();

    /**
     * @param FieldModel $field
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(FieldModel $field, TranslationEntityModel $translation_entity)
    {
        parent::__construct();

        $this->field = $field;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Displays the field overview page
     */
    public function listField()
    {
        $this->actionListField();
        $this->setTitleListField();
        $this->setBreadcrumbListField();
        $this->setFilterListField();
        $this->setPagerListField();

        $this->setData('fields', $this->getListField());
        $this->setData('widget_types', $this->field->getWidgetTypes());

        $this->outputListField();
    }

    /**
     * Sets filter on the field overview page
     */
    protected function setFilterListField()
    {
        $allowed = array('title', 'type', 'widget', 'field_id');
        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected fields
     */
    protected function actionListField()
    {
        list($selected, $action) = $this->getPostedAction();

        $deleted = 0;

        foreach ($selected as $field_id) {
            if ($action === 'delete' && $this->access('field_delete')) {
                $deleted += (int) $this->field->delete($field_id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Set pager
     * @return array
     */
    protected function setPagerListField()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->field->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of fields
     * @return array
     */
    protected function getListField()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        return (array) $this->field->getList($conditions);
    }

    /**
     * Sets titles on the field overview page
     */
    protected function setTitleListField()
    {
        $this->setTitle($this->text('Fields'));
    }

    /**
     * Sets breadcrumbs on the field overview page
     */
    protected function setBreadcrumbListField()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the field overview page
     */
    protected function outputListField()
    {
        $this->output('content/field/list');
    }

    /**
     * Displays the field edit form
     * @param integer|null $field_id
     */
    public function editField($field_id = null)
    {
        $this->setField($field_id);
        $this->setTitleEditField();
        $this->setBreadcrumbEditField();

        $this->setData('field', $this->data_field);
        $this->setData('types', $this->field->getTypes());
        $this->setData('can_delete', $this->canDeleteField());
        $this->setData('widget_types', $this->field->getWidgetTypes());
        $this->setData('languages', $this->language->getList(array('in_database' => true)));

        $this->submitEditField();
        $this->outputEditField();
    }

    /**
     * Handles a submitted field data
     */
    protected function submitEditField()
    {
        if ($this->isPosted('delete')) {
            $this->deleteField();
        } else if ($this->isPosted('save') && $this->validateEditField()) {
            if (isset($this->data_field['field_id'])) {
                $this->updateField();
            } else {
                $this->addField();
            }
        }
    }

    /**
     * Validates an array of submitted field data
     * @return bool
     */
    protected function validateEditField()
    {
        $this->setSubmitted('field');
        $this->setSubmitted('update', $this->data_field);

        $this->validateComponent('field');

        return !$this->hasErrors();
    }

    /**
     * Whether the field can be deleted
     * @return bool
     */
    protected function canDeleteField()
    {
        return isset($this->data_field['field_id'])
            && $this->field->canDelete($this->data_field['field_id'])
            && $this->access('field_delete');
    }

    /**
     * Set a field data
     * @param integer $field_id
     */
    protected function setField($field_id)
    {
        $this->data_field = array();

        if (is_numeric($field_id)) {

            $conditions = array(
                'language' => 'und',
                'field_id' => $field_id
            );

            $this->data_field = $this->field->get($conditions);

            if (empty($this->data_field)) {
                $this->outputHttpStatus(404);
            }

            $this->prepareField($this->data_field);
        }
    }

    /**
     * Prepare an array of field data
     * @param array $field
     */
    protected function prepareField(array &$field)
    {
        $this->setItemTranslation($field, 'field', $this->translation_entity);
    }

    /**
     * Deletes a field
     */
    protected function deleteField()
    {
        $this->controlAccess('field_delete');

        if ($this->field->delete($this->data_field['field_id'])) {
            $this->redirect('admin/content/field', $this->text('Field has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Field has not been deleted'), 'warning');
    }

    /**
     * Updates a field
     */
    protected function updateField()
    {
        $this->controlAccess('field_edit');

        if ($this->field->update($this->data_field['field_id'], $this->getSubmitted())) {
            $this->redirect('admin/content/field', $this->text('Field has been updated'), 'success');
        }

        $this->redirect('', $this->text('Field has not been updated'), 'warning');
    }

    /**
     * Adds a new field
     */
    protected function addField()
    {
        $this->controlAccess('field_add');

        if ($this->field->add($this->getSubmitted())) {
            $this->redirect('admin/content/field', $this->text('Field has been added'), 'success');
        }

        $this->redirect('', $this->text('Field has not been added'), 'warning');
    }

    /**
     * Sets title on the field edit form
     */
    protected function setTitleEditField()
    {
        if (isset($this->data_field['field_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_field['title']));
        } else {
            $title = $this->text('Add field');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the field edit form
     */
    protected function setBreadcrumbEditField()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/field'),
            'text' => $this->text('Fields')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the field edit page
     */
    protected function outputEditField()
    {
        $this->output('content/field/edit');
    }

}
