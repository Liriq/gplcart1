<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Product as ProductModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate product comparison data
 */
class Compare extends BaseValidator
{

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ProductModel $product
     */
    public function __construct(ProductModel $product)
    {
        parent::__construct();

        $this->product = $product;
    }

    /**
     * Performs full product comparison data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function compare(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateProductCompare($options);
        return $this->getResult();
    }

    /**
     * Validates a compared product ID
     * @param array $options
     * @return boolean
     */
    protected function validateProductCompare(array $options)
    {
        $product_id = $this->getSubmitted('product_id', $options);

        if (empty($product_id)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        if (!is_numeric($product_id)) {
            $vars = array('@field' => $this->language->text('Product'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $vars = array('@name' => $this->language->text('Product'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('product_id', $error, $options);
            return false;
        }

        $this->setSubmitted('product', $product);
        return true;
    }

}
