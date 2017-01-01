<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Cache;
use gplcart\core\Model;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to payment
 */
class Payment extends Model
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
     * Returns an array of payment methods
     * @param boolean $enabled
     * @return array
     */
    public function getList($enabled = false)
    {
        $methods = &Cache::memory('payment.method');

        if (isset($methods)) {
            return $methods;
        }

        $methods = $this->getDefault();

        $this->hook->fire('payment.method', $methods);

        if ($enabled) {
            $methods = array_filter($methods, function ($method) {
                return !empty($method['status']);
            });
        }

        gplcart_array_sort($methods);
        return $methods;
    }

    /**
     * Returns a payment method
     * @param string $method_id
     * @return array
     */
    public function get($method_id)
    {
        $methods = $this->getList();
        return empty($methods[$method_id]) ? array() : $methods[$method_id];
    }

    /**
     * Returns an array of default payment methods
     * @return array
     */
    protected function getDefault()
    {
        $methods = array();

        $methods['cod'] = array(
            'title' => $this->language->text('Cash on delivery'),
            'description' => $this->language->text('Payment for an order is made at the time of delivery'),
            'template' => array('complete' => ''),
            'image' => '',
            'status' => true,
            'weight' => 0
        );

        return $methods;
    }

}
