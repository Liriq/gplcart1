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
use gplcart\core\models\Review as ReviewModel;

/**
 * Provides methods to validate reviews
 */
class Review extends Component
{

    /**
     * Review model instance
     * @var \gplcart\core\models\Review $review
     */
    protected $review;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * @param ReviewModel $review
     * @param ProductModel $product
     */
    public function __construct(ReviewModel $review, ProductModel $product)
    {
        parent::__construct();

        $this->review = $review;
        $this->product = $product;
    }

    /**
     * Performs full review data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function review(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateReview();
        $this->validateBool('status');
        $this->validateTextReview();
        $this->validateCreatedReview();
        $this->validateProductReview();
        $this->validateEmailReview();
        $this->validateUserId();

        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates a review to be updated
     * @return boolean|null
     */
    protected function validateReview()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->review->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Review'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a review text
     * @return boolean|null
     */
    protected function validateTextReview()
    {
        $field = 'text';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Text');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $length = mb_strlen($value);
        list($min, $max) = $this->review->getLimits();

        if ($length < $min || $length > $max) {
            $this->setErrorLengthRange($field, $label, $min, $max);
            return false;
        }

        return true;
    }

    /**
     * Validates a created review date
     * @return boolean|null
     */
    protected function validateCreatedReview()
    {
        $field = 'created';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $timestamp = strtotime($value);

        if (empty($timestamp)) {
            $this->setErrorInvalid($field, $this->translation->text('Created'));
            return false;
        }

        $this->setSubmitted('created', $timestamp);
        return true;
    }

    /**
     * Validates a product ID
     * @return boolean|null
     */
    protected function validateProductReview()
    {
        $field = 'product_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Product');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $product = $this->product->get($value);

        if (empty($product['product_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a user E-mail
     * @return boolean|null
     */
    protected function validateEmailReview()
    {
        $field = 'email';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Email');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user['user_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('user_id', $user['user_id']);
        return true;
    }

}
