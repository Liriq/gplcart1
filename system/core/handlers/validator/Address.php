<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\City as CityModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate address data
 */
class Address extends BaseValidator
{

    /**
     * City model instance
     * @var \gplcart\core\models\City $city
     */
    protected $city;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Constructor
     * @param CountryModel $country
     * @param StateModel $state
     * @param AddressModel $address
     * @param CityModel $city
     */
    public function __construct(CountryModel $country, StateModel $state,
            AddressModel $address, CityModel $city)
    {
        parent::__construct();

        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->address = $address;
    }

    /**
     * Performs full address data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function address(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateAddress();
        $this->validateCountryAddress();
        $this->validateStateAddress();
        $this->validateCityAddress();
        $this->validateTypeAddress();
        $this->validateTextFieldsAddress();
        $this->validateUserId();

        // No more needed, remove
        $this->unsetSubmitted('format');

        return $this->getResult();
    }

    /**
     * Validates an address to be updated
     * @return boolean
     */
    protected function validateAddress()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->address->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Address'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a country code
     * @return boolean|null
     */
    protected function validateCountryAddress()
    {
        $code = $this->getSubmitted('country', $this->options);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Country'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('country', $error, $this->options);
            return false;
        }

        $country = $this->country->get($code);

        if (empty($country['code'])) {
            $vars = array('@name' => $this->language->text('Country'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('country', $error, $this->options);
            return false;
        }

        $format = $this->country->getFormat($code, true);
        $this->setSubmitted('format', $format);
        return true;
    }

    /**
     * Validates a country state
     * @return boolean|null
     */
    protected function validateStateAddress()
    {
        $format = $this->getSubmitted('format');
        $state_id = $this->getSubmitted('state_id', $this->options);

        if (!isset($state_id) || empty($format)) {
            return null;
        }

        if (empty($state_id) && !empty($format['state_id']['required'])) {
            $vars = array('@field' => $this->language->text('State'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('state_id', $error, $this->options);
            return false;
        }

        if (!is_numeric($state_id)) {
            $vars = array('@field' => $this->language->text('State'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('state_id', $error, $this->options);
            return false;
        }

        if (empty($state_id)) {
            return true;
        }

        $state = $this->state->get($state_id);

        if (empty($state['state_id'])) {
            $vars = array('@name' => $this->language->text('State'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('state_id', $error, $this->options);
            return false;
        }

        return true;
    }

    /**
     * Validates a city
     * @return boolean|null
     */
    protected function validateCityAddress()
    {
        $city_id = $this->getSubmitted('city_id', $this->options);

        if ($this->isUpdating() && !isset($city_id)) {
            return null;
        }

        $format = $this->getSubmitted('format');

        if (empty($format)) {
            return null;
        }

        if (empty($city_id)) {

            if (!empty($format['city_id']['required'])) {
                $vars = array('@field' => $this->language->text('City'));
                $error = $this->language->text('@field is required', $vars);
                $this->setError('city_id', $error, $this->options);
                return false;
            }

            return true;
        }

        if (is_numeric($city_id)) {
            $city = $this->city->get($city_id);
            if (empty($city)) {
                $vars = array('@name' => $this->language->text('City'));
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError('city_id', $error, $this->options);
                return false;
            }
            return true;
        }

        if (mb_strlen($city_id) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('City'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('city_id', $error, $this->options);
            return false;
        }

        return true;
    }

    /**
     * Validates an address type
     * @return boolean|null
     */
    protected function validateTypeAddress()
    {
        $type = $this->getSubmitted('type', $this->options);

        if (!isset($type)) {
            return null;
        }

        $types = $this->address->getTypes();

        if (!in_array($type, $types)) {
            $vars = array('@name' => $this->language->text('Type'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('type', $error, $this->options);
            return false;
        }

        return true;
    }

    /**
     * Validates address text fields
     * @return boolean|null
     */
    protected function validateTextFieldsAddress()
    {
        $format = $this->getSubmitted('format');

        if (empty($format) || $this->isError('country', $this->options)) {
            return null;
        }

        $fields = array('address_1', 'address_2', 'phone', 'middle_name',
            'last_name', 'first_name', 'postcode', 'company', 'fax');

        foreach ($fields as $field) {

            if (empty($format[$field])) {
                continue;
            }

            $submitted_value = $this->getSubmitted($field, $this->options);

            if ($this->isUpdating() && !isset($submitted_value)) {
                continue;
            }

            if (empty($format[$field]['required'])) {
                continue;
            }

            if (!isset($submitted_value)//
                    || $submitted_value === ''//
                    || mb_strlen($submitted_value) > 255) {

                $vars = array('@min' => 1, '@max' => 255, '@field' => $format[$field]['name']);
                $error = $this->language->text('@field must be @min - @max characters long', $vars);
                $this->setError($field, $error, $this->options);
            }
        }

        return empty($error);
    }

}
