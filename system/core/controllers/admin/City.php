<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Country;
use core\models\State;
use core\models\City as C;

class City extends Controller
{

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * City model instance
     * @var \core\models\City $city
     */
    protected $city;

    /**
     * Constructor
     * @param Country $country
     * @param State $state
     * @param C $city
     */
    public function __construct(Country $country, State $state, C $city)
    {
        parent::__construct();

        $this->country = $country;
        $this->state = $state;
        $this->city = $city;
    }

    /**
     * Displays the city overview page
     */
    public function cities($country_code, $state_id)
    {
        $country = $this->getCountry($country_code);
        $state = $this->getState($state_id);

        $action = $this->request->post('action');
        $value = $this->request->post('value');
        $selected = $this->request->post('selected', array());

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $limit = $this->setPager($this->getTotalCities($state_id, $query), $query);

        $this->data['country'] = $country;
        $this->data['state'] = $state;
        $this->data['cities'] = $this->getCities($limit, $query, $state_id);

        $this->setFilter(array('city_id', 'name', 'status'), $query);

        $this->setTitleCities($state);
        $this->setBreadcrumbCities($country);
        $this->outputCities();
    }

    /**
     * Returns total number of cities for pager
     * @param integer $state_id
     * @param array $query
     * @return integer
     */
    protected function getTotalCities($state_id, $query)
    {
        return $this->city->getList(array('count' => true, 'state_id' => $state_id) + $query);
    }

    /**
     * Renders the city overview page
     */
    protected function outputCities()
    {
        $this->output('settings/city/list');
    }

    /**
     * Returns an array of country data
     * @param string $country_code
     * @return array
     */
    protected function getCountry($country_code)
    {
        $country = $this->country->get($country_code);

        if ($country) {
            return $country;
        }

        $this->outputError(404);
    }

    /**
     * Returns an array of state data
     * @param integer $state_id
     * @return array
     */
    protected function getState($state_id)
    {
        $state = $this->state->get($state_id);

        if ($state) {
            return $state;
        }

        $this->outputError(404);
    }

    /**
     * Returns an array of cities
     * @param integer $state_id
     * @return array
     */
    protected function getCities($limit, $query, $state_id)
    {
        return $this->city->getList(array('limit' => $limit, 'state_id' => $state_id) + $query);
    }

    /**
     * Sets title on the city overview page
     * @param array $state
     */
    protected function setTitleCities($state)
    {
        $this->setTitle($this->text('Cities of state %state', array('%state' => $state['name'])));
    }

    /**
     * Sets breadcrumbs on the city overview page
     * @param array $country
     */
    protected function setBreadcrumbCities($country)
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')));

        $this->setBreadcrumb(array(
            'url' => $this->url("admin/settings/states/{$country['code']}"),
            'text' => $this->text('States of %country', array('%country' => $country['name']))));
    }

    /**
     * Displays the city edit page
     * @param string $country_code
     * @param integer $state_id
     * @param integer $city_id
     */
    public function edit($country_code, $state_id, $city_id = null)
    {
        $country = $this->getCountry($country_code);
        $state = $this->getState($state_id);
        $city = $this->get($city_id);

        $this->data['country'] = $country;
        $this->data['state'] = $state;
        $this->data['city'] = $city;

        if ($this->request->post('delete')) {
            $this->delete($country, $state, $city);
        }

        if ($this->request->post('save')) {
            $this->submit($country, $state, $city);
        }

        $this->setTitleCity($city);
        $this->setBreadcrumbCity();
        $this->outputEdit();
    }

    /**
     * Renders the city edit page
     */
    protected function outputEdit()
    {
        $this->output('settings/city/edit');
    }

    /**
     * Sets titles on the city edit page
     * @param array $city
     */
    protected function setTitleCity($city)
    {
        if (isset($city['city_id'])) {
            $title = $this->text('Edit city %name', array('%name' => $city['name']));
        } else {
            $title = $this->text('Add city');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the city edit page
     */
    protected function setBreadcrumbCity()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/country'), 'text' => $this->text('Countries')));
    }

    /**
     * Returns an array of city data
     * @param integer $city_id
     * @return array
     */
    protected function get($city_id)
    {
        if (!is_numeric($city_id)) {
            return array();
        }

        $city = $this->city->get($city_id);
        if ($city) {
            return $city;
        }

        $this->outputError(404);
    }

    /**
     * Deletes a city
     * @param array $country
     * @param array $state
     * @param array $city
     * @return null
     */
    protected function delete($country, $state, $city)
    {
        if (empty($city['city_id'])) {
            return;
        }

        $this->controlAccess('city_delete');
        if ($this->city->delete($city['city_id'])) {
            $this->redirect("admin/settings/cities/{$country['code']}/{$state['state_id']}", $this->text('City %name has been deleted', array(
                        '%name' => $city['name'])), 'success');
        }

        $this->redirect('', $this->text('Unable to delete city %name. The most probable reason - it is already used in addresses', array(
                    '%name' => $city['name'])), 'warning');
    }

    /**
     * Applies an action to the selected cities
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        $deleted = $updated = 0;
        foreach ($selected as $id) {
            if ($action == 'status' && $this->access('city_edit')) {
                $updated += (int) $this->city->update($id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('city_delete')) {
                $deleted += (int) $this->city->delete($id);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Cities have been updated'), 'success');
            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Cities have been deleted'), 'success');
            return true;
        }

        return false;
    }

    /**
     * Saves an array of submitted city
     * @param array $country
     * @param array $state
     * @param array $city
     * @return null
     */
    protected function submit($country, $state, $city)
    {
        $this->submitted = $this->request->post('city', array());
        $this->validate();

        if ($this->formErrors()) {
            $this->data['city'] = $this->submitted;
            return;
        }

        $this->submitted += array('country' => $country['code'], 'state_id' => $state['state_id']);

        if (isset($city['city_id'])) {
            $this->controlAccess('city_edit');
            $this->city->update($city['city_id'], $this->submitted);
            $this->redirect("admin/settings/cities/{$country['code']}/{$state['state_id']}", $this->text('City %name has been updated', array(
                        '%name' => $city['name'])), 'success');
        }

        $this->controlAccess('city_add');
        $this->city->add($this->submitted);
        $this->redirect("admin/settings/cities/{$country['code']}/{$state['state_id']}", $this->text('City has been added'), 'success');
    }

    /**
     * Validates an array of submitted city data
     */
    protected function validate()
    {
        $this->validateTitle();
    }

    /**
     * Validates a city name
     */
    protected function validateTitle()
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
        }
    }
}
