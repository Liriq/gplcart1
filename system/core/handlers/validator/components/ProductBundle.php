<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Product as ProductModel;

/**
 * Provides methods to validate a product bundle data
 */
class ProductBundle extends Component
{

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * @param ProductModel $product
     */
    public function __construct(ProductModel $product)
    {
        parent::__construct();

        $this->product = $product;
    }

    /**
     * Performs validation of submitted product bundle data
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function productBundle(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductProductBundle();
        $this->validateItemsProductBundle();

        return $this->getResult();
    }

    /**
     * Validates product that contains bundled items
     * @return boolean
     */
    protected function validateProductProductBundle()
    {
        $field = 'product_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Product');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('product', $product);
        return true;
    }

    /**
     * Validates bundled products
     * @return boolean|null
     */
    protected function validateItemsProductBundle()
    {
        $field = 'bundle';
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        if (!is_array($value)) {
            $this->setErrorInvalid($field, $this->translation->text('Product bundle'));
            return false;
        }

        $main_product = $this->getSubmitted('product');

        $loaded = array();

        foreach ($value as $product_id) {

            if (isset($loaded[$product_id])) {
                $this->setError($field, $this->translation->text('All bundled products must be unique'));
                return false;
            }

            $product = $this->product->get($product_id);

            $loaded[$product_id] = $product;

            if (empty($product['status'])) {
                $this->setError($field, $this->translation->text('Some of bundled products are either disabled or non-existing'));
                return false;
            }

            if ($main_product['product_id'] == $product['product_id']) {
                $this->setError($field, $this->translation->text('Bundled products cannot be the same as the main product'));
                return false;
            }

            if ($main_product['store_id'] != $product['store_id']) {
                $this->setError($field, $this->translation->text('All bundled products must belong to the same store'));
                return false;
            }
        }

        $this->setSubmitted('products', $loaded);
        return true;
    }

}
