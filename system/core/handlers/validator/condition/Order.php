<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Payment;
use gplcart\core\models\Shipping;
use gplcart\core\models\Translation;

/**
 * Contains methods to validate various order conditions
 */
class Order
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * @param Payment $payment
     * @param Shipping $shipping
     * @param Translation $translation
     */
    public function __construct(Payment $payment, Shipping $shipping, Translation $translation)
    {
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->translation = $translation;
    }

    /**
     * Validates the shipping method condition
     * @param array $values
     * @return boolean|string
     */
    public function shippingMethod(array $values)
    {
        $existing = array_filter($values, function ($method_id) {
            return (bool) $this->shipping->get($method_id);
        });

        if (count($values) != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Shipping')));
        }

        return true;
    }

    /**
     * Validates the payment method condition
     * @param array $values
     * @return boolean|string
     */
    public function paymentMethod(array $values)
    {
        $existing = array_filter($values, function ($method_id) {
            return (bool) $this->payment->get($method_id);
        });

        if (count($values) != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Payment')));
        }

        return true;
    }

}
