<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\price_rule;

use gplcart\core\models\Currency;
use gplcart\core\models\Price;

/**
 * Contains callback methods to modify prices depending on the price rule type
 */
class Type
{

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * @param Price $price
     * @param Currency $currency
     */
    public function __construct(Price $price, Currency $currency)
    {
        $this->price = $price;
        $this->currency = $currency;
    }

    /**
     * Adds a percent price rule value to the original amount
     * @param int $amount
     * @param array $components
     * @param array $price_rule
     * @return int
     */
    public function percent(&$amount, array &$components, array $price_rule)
    {
        $value = $amount * ($price_rule['value'] / 100);
        $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => $value);
        return $amount += $value;
    }

    /**
     * Adds a fixed price rule value to the original amount
     * @param int $amount
     * @param array $components
     * @param array $price_rule
     * @param array $data
     * @return int
     */
    public function fixed(&$amount, array &$components, array $price_rule, array $data)
    {
        $value = $this->convertValue($price_rule, $data['currency']);
        $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => $value);
        return $amount += $value;
    }

    /**
     * Sets a final amount using the price rule value
     * @param int $amount
     * @param array $components
     * @param array $price_rule
     * @param array $data
     * @return int
     */
    public function finalAmount(&$amount, array &$components, array $price_rule, array $data)
    {
        $value = $this->convertValue($price_rule, $data['currency']);
        $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => $value);
        return $amount = $value;
    }

    /**
     * Converts a price rule value to the minor units considering the currency
     * @param array $price_rule
     * @param string $currency
     * @return int
     */
    protected function convertValue(array $price_rule, $currency)
    {
        $amount = $this->price->amount(abs($price_rule['value']), $price_rule['currency']);
        $converted = $this->currency->convert($amount, $price_rule['currency'], $currency);
        return $price_rule['value'] < 0 ? -$converted : $converted;
    }

}
