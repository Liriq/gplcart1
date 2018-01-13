<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Zone as ZoneModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel;
use gplcart\core\handlers\validator\BaseComponent as BaseComponentValidator;

/**
 * Provides methods to validate country states data
 */
class State extends BaseComponentValidator
{

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * @param StateModel $state
     * @param CountryModel $country
     * @param ZoneModel $zone
     */
    public function __construct(StateModel $state, CountryModel $country, ZoneModel $zone)
    {
        parent::__construct();

        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
    }

    /**
     * Performs full country state validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function state(array $submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateState();
        $this->validateStatus();
        $this->validateCodeState();
        $this->validateName();
        $this->validateCountryState();
        $this->validateZoneState();

        return $this->getResult();
    }

    /**
     * Validates a state to be updated
     * @return boolean|null
     */
    protected function validateState()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->state->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('State'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates country code
     * @return boolean|null
     */
    protected function validateCountryState()
    {
        $field = 'country';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Country');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $country = $this->country->get($value);

        if (empty($country)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a state code
     * @return boolean
     */
    public function validateCodeState()
    {
        $field = 'code';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Code');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        $updating = $this->getUpdating();
        if (isset($updating['code']) && $updating['code'] === $value) {
            return true;
        }

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $existing = $this->state->getList(array('code' => $value, 'country' => $this->getSubmitted('country')));

        if (!empty($existing)) {
            $this->setErrorExists($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a zone ID
     * @return boolean|null
     */
    protected function validateZoneState()
    {
        $field = 'zone_id';
        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Zone');

        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $zone = $this->zone->get($value);

        if (empty($zone)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

}
