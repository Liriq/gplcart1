<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\Container;

/**
 * Provides methods to check address conditions
 */
class Address extends Base
{

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * City model instance
     * @var \gplcart\core\models\City $city
     */
    protected $city;

    /**
     * State model instance
     * @var \gplcart\core\models\CountryState $state
     */
    protected $state;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->city = Container::get('gplcart\\core\\models\\City');
        $this->state = Container::get('gplcart\\core\\models\\CountryState');
        $this->country = Container::get('gplcart\\core\\models\\Country');
        $this->address = Container::get('gplcart\\core\\models\\Address');
    }

    /**
     * Whether the zone ID condition is met
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function zoneId(array $condition, array $data, $key)
    {
        if (isset($data['data']['order'][$key])) {
            return $this->checkZoneIdByAddressId($condition, $data, $key);
        }

        return $this->checkZoneIdByAddressData($condition, $data);
    }

    /**
     * Whether the country condition is met
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function countryCode(array $condition, array $data, $key)
    {
        // Check form fields
        if (!empty($data['data']['address']['country'])) {
            $country = $data['data']['address']['country'];
            return $this->compare($country, $condition['value'], $condition['operator']);
        }

        if (empty($data['data']['order'][$key])) {
            return false;
        }

        $address = $this->address->get($data['data']['order'][$key]);

        if (empty($address['country'])) {
            return false;
        }

        return $this->compare($address['country'], $condition['value'], $condition['operator']);
    }

    /**
     * Whether the state ID condition is met
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function stateId(array $condition, array $data, $key)
    {
        // Check form fields
        if (isset($data['data']['address']['state_id'])) {
            $state_id = $data['data']['address']['state_id'];
            return $this->compare($state_id, $condition['value'], $condition['operator']);
        }

        if (!isset($data['data']['order'][$key])) {
            return false;
        }

        $address = $this->address->get($data['data']['order'][$key]);

        if (empty($address['state_id'])) {
            return false;
        }

        return $this->compare($address['state_id'], $condition['value'], $condition['operator']);
    }

    /**
     * Whether the state ID condition is met using an existing address
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    protected function checkZoneIdByAddressId($condition, $data, $key)
    {
        $address = $this->address->get($data['data']['order'][$key]);

        if (empty($address)) {
            return false;
        }

        $fields = array('country_zone_id', 'state_zone_id', 'city_zone_id');

        $ids = array();

        foreach ($fields as $field) {
            $ids[] = $address[$field];
        }

        return $this->compare($ids, $condition['value'], $condition['operator']);
    }

    /**
     * Whether the state ID condition is met using form fields
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    protected function checkZoneIdByAddressData($condition, $data)
    {
        if (empty($data['data']['order']['address'])) {
            return false;
        }

        $ids = $this->getAddressZoneId($data['data']['order']['address']);
        return $this->compare($ids, $condition['value'], $condition['operator']);
    }

    /**
     * Returns an array of zone IDs from address components
     * @param array $address
     * @return array
     */
    protected function getAddressZoneId(array $address)
    {
        $result = array();

        foreach (array('state_id', 'city_id', 'country') as $field) {

            if (empty($address[$field])) {
                continue;
            }

            if ($field === 'city_id') {
                $data = $this->city->get($address[$field]);
            } else if ($field === 'state_id') {
                $data = $this->state->get($address[$field]);
            } else if ($field === 'country') {
                $data = $this->country->get($address[$field]);
            }

            if (!empty($data['zone_id'])) {
                $result[] = $data['zone_id'];
            }
        }

        return $result;
    }

    /**
     * Sets a property
     * @param string $name
     * @param mixed $value
     */
    public function setProperty($name, $value)
    {
        $this->{$name} = $value;
    }

}
