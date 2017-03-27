<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to countries
 */
class Country extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of country format
     * @param string|array $country
     * @param bool $only_enabled
     * @return array
     */
    public function getFormat($country, $only_enabled = false)
    {
        $data = is_string($country) ? $this->get($country) : (array) $country;

        if (empty($data['format'])) {
            $format = $this->getDefaultFormat();
            gplcart_array_sort($format);
        } else {
            $format = $data['format'];
        }

        if ($only_enabled) {
            return array_filter($format, function ($item) {
                return !empty($item['status']);
            });
        }

        return $format;
    }

    /**
     * Loads a country from the database
     * @param string $code
     * @return array
     */
    public function get($code)
    {
        $country = &Cache::memory(__METHOD__ . $code);

        if (isset($country)) {
            return $country;
        }

        $this->hook->fire('country.get.before', $code);

        $sql = 'SELECT * FROM country WHERE code=?';
        $options = array('unserialize' => 'format');
        $country = $this->db->fetch($sql, array($code), $options);

        if (!empty($country)) {
            $default_format = $this->getDefaultFormat();
            $country['format'] = gplcart_array_merge($default_format, $country['format']);
            gplcart_array_sort($country['format']);
        }

        $this->hook->fire('country.get.after', $code, $country);
        return $country;
    }

    /**
     * Returns the default country format
     * @return array
     */
    public function getDefaultFormat()
    {
        $items = array();

        $items['country'] = array(
            'name' => $this->language->text('Country'),
            'required' => 0,
            'weight' => 0,
            'status' => 1
        );

        $items['state_id'] = array(
            'name' => $this->language->text('State'),
            'required' => 0,
            'weight' => 1,
            'status' => 1
        );

        $items['city_id'] = array(
            'name' => $this->language->text('City'),
            'required' => 1,
            'weight' => 2,
            'status' => 1
        );

        $items['address_1'] = array(
            'name' => $this->language->text('Address'),
            'required' => 1,
            'weight' => 3,
            'status' => 1
        );

        $items['address_2'] = array(
            'name' => $this->language->text('Additional address'),
            'required' => 0,
            'weight' => 4,
            'status' => 0
        );

        $items['phone'] = array(
            'name' => $this->language->text('Phone'),
            'required' => 1,
            'weight' => 5,
            'status' => 1
        );

        $items['postcode'] = array(
            'name' => $this->language->text('Post code'),
            'required' => 1,
            'weight' => 6,
            'status' => 1
        );

        $items['first_name'] = array(
            'name' => $this->language->text('First name'),
            'required' => 1,
            'weight' => 7,
            'status' => 1
        );

        $items['middle_name'] = array(
            'name' => $this->language->text('Middle name'),
            'required' => 1,
            'weight' => 8,
            'status' => 1
        );

        $items['last_name'] = array(
            'name' => $this->language->text('Last name'),
            'required' => 1,
            'weight' => 9,
            'status' => 1
        );

        $items['company'] = array(
            'name' => $this->language->text('Company'),
            'required' => 0,
            'weight' => 10,
            'status' => 0
        );

        $items['fax'] = array(
            'name' => $this->language->text('Fax'),
            'required' => 0,
            'weight' => 11,
            'status' => 0
        );

        return $items;
    }

    /**
     * Adds a country
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('country.add.before', $data);

        if (empty($data['code'])) {
            return false;
        }

        $result = true;
        $this->db->insert('country', $data);
        $this->hook->fire('country.add.after', $data, $result);

        return (bool) $result;
    }

    /**
     * Updates a country
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $this->hook->fire('country.update.before', $code, $data);

        if (empty($code) || empty($data)) {
            return false;
        }

        unset($data['code']); // Cannot update primary key
        $result = $this->db->update('country', $data, array('code' => $code));

        $this->hook->fire('country.update.after', $code, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a country
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $this->hook->fire('country.delete.before', $code);

        if (empty($code)) {
            return false;
        }

        if (!$this->canDelete($code)) {
            return false;
        }

        $result = (bool) $this->db->delete('country', array('code' => $code));

        if ($result) {
            $this->db->delete('city', array('country' => $code));
            $this->db->delete('state', array('country' => $code));
        }

        $this->hook->fire('country.delete.after', $code, $result);
        return (bool) $result;
    }

    /**
     * Returns true if the country can be deleted
     * @param string $code
     * @return boolean
     */
    public function canDelete($code)
    {
        $sql = 'SELECT address_id FROM address WHERE country=?';
        $result = $this->db->fetchColumn($sql, array($code));

        return empty($result);
    }

    /**
     * Returns an array of country names
     * @param boolean $enabled
     * @return array
     */
    public function getNames($enabled = false)
    {
        $countries = (array) $this->getList(array('status' => $enabled));

        $names = array();
        foreach ($countries as $code => $country) {
            $names[$code] = $country['native_name'];
        }

        return $names;
    }

    /**
     * Returns an array of countries
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $list = &Cache::memory(array(__METHOD__ => $data));

        if (isset($list)) {
            return $list;
        }

        $sql = 'SELECT * ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(code)';
        }

        $sql .= ' FROM country WHERE LENGTH(code) > 0';

        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['native_name'])) {
            $sql .= ' AND native_name LIKE ?';
            $where[] = "%{$data['native_name']}%";
        }

        if (isset($data['code'])) {
            $sql .= ' AND code LIKE ?';
            $where[] = "%{$data['code']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'native_name', 'code', 'status', 'weight');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order'])//
                && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $results = $this->db->fetchAll($sql, $where);

        $list = array();
        foreach ($results as $country) {
            $country['format'] = unserialize($country['format']);
            $list[$country['code']] = $country;
            $list[$country['code']]['format'] += $this->getDefaultFormat();
        }

        $this->hook->fire('country.list', $list);
        return $list;
    }

    /**
     * Returns an array of country names regarding to ISO 3166-1 alpha-2 standard
     * or a string with a country name if the code parameter is set
     * @param null|string $code
     * @return array|string
     */
    public function getIso($code = null)
    {
        $data = include GC_CONFIG_COUNTRY;

        if (isset($code)) {
            return isset($data[$code]) ? $data[$code] : '';
        }

        return $data;
    }

}
