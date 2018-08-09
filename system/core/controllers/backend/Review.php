<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Review as ReviewModel;

/**
 * Handles incoming requests and outputs data related to user reviews
 */
class Review extends Controller
{

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

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
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of review data
     * @var array
     */
    protected $data_review = array();

    /**
     * @param ReviewModel $review
     * @param ProductModel $product
     * @param PriceModel $price
     */
    public function __construct(ReviewModel $review, ProductModel $product, PriceModel $price)
    {
        parent::__construct();

        $this->price = $price;
        $this->review = $review;
        $this->product = $product;
    }

    /**
     * Displays the reviews overview page
     */
    public function listReview()
    {
        $this->actionListReview();
        $this->setTitleListReview();
        $this->setBreadcrumbListReview();
        $this->setFilterListReview();
        $this->setPagerListReview();

        $this->setData('reviews', $this->getListReview());

        $this->outputListReview();
    }

    /**
     * Set filter on the reviews overview page
     */
    protected function setFilterListReview()
    {
        $allowed = array('product_id', 'email_like',
            'status', 'created', 'text', 'review_id', 'product_title');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected reviews
     */
    protected function actionListReview()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $updated = $deleted = 0;

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('review_edit')) {
                $updated += (int) $this->review->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('review_delete')) {
                $deleted += (int) $this->review->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 1) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListReview()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->review->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of reviews
     * @return array
     */
    protected function getListReview()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        return (array) $this->review->getList($conditions);
    }

    /**
     * Sets title on the reviews overview page
     */
    protected function setTitleListReview()
    {
        $this->setTitle($this->text('Reviews'));
    }

    /**
     * Sets breadcrumbs on the reviews overview page
     */
    protected function setBreadcrumbListReview()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the reviews overview page
     */
    protected function outputListReview()
    {
        $this->output('content/review/list');
    }

    /**
     * Displays the review edit form
     * @param integer|null $review_id
     */
    public function editReview($review_id = null)
    {
        $this->setReview($review_id);
        $this->setTitleEditReview();
        $this->setBreadcrumbEditReview();

        $this->setData('review', $this->data_review);

        $this->submitEditReview();
        $this->setDataUserEditReview();
        $this->setDataProductEditReview();
        $this->outputEditReview();
    }

    /**
     * Set a review data
     * @param integer $review_id
     */
    protected function setReview($review_id)
    {
        $this->data_review = array();

        if (is_numeric($review_id)) {

            $this->data_review = $this->review->get($review_id);

            if (empty($this->data_review)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted review
     */
    protected function submitEditReview()
    {
        if ($this->isPosted('delete')) {
            $this->deleteReview();
        } else if ($this->isPosted('save') && $this->validateEditReview()) {
            if (isset($this->data_review['review_id'])) {
                $this->updateReview();
            } else {
                $this->addReview();
            }
        }
    }

    /**
     * Validates a submitted review
     * @return bool
     */
    protected function validateEditReview()
    {
        $this->setSubmitted('review');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_review);

        $this->validateComponent('review');

        return !$this->hasErrors();
    }

    /**
     * Deletes a review
     */
    protected function deleteReview()
    {
        $this->controlAccess('review_delete');

        if ($this->review->delete($this->data_review['review_id'])) {
            $this->redirect('admin/content/review', $this->text('Review has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Review has not been deleted'), 'warning');
    }

    /**
     * Updates a review
     */
    protected function updateReview()
    {
        $this->controlAccess('review_edit');

        if ($this->review->update($this->data_review['review_id'], $this->getSubmitted())) {
            $this->redirect('admin/content/review', $this->text('Review has been updated'), 'success');
        }

        $this->redirect('', $this->text('Review has not been updated'), 'warning');
    }

    /**
     * Adds a new review
     */
    protected function addReview()
    {
        $this->controlAccess('review_add');

        if ($this->review->add($this->getSubmitted())) {
            $this->redirect('admin/content/review', $this->text('Review has been added'), 'success');
        }

        $this->redirect('', $this->text('Review has not been added'), 'warning');
    }

    /**
     * Set user template data
     */
    protected function setDataUserEditReview()
    {
        $user = $this->user->get($this->getData('review.user_id'));

        if (isset($user['email'])) {
            $this->setData('review.email', $user['email']);
        }
    }

    /**
     * Set product template data
     */
    protected function setDataProductEditReview()
    {
        $product_id = $this->getData('review.product_id');

        $products = array();

        if (!empty($product_id)) {

            $product = $this->product->get($product_id);

            $options = array(
                'entity' => 'product',
                'entity_id' => $product_id,
                'template_item' => 'backend|content/product/suggestion'
            );

            $this->setItemThumb($product, $this->image, $options);
            $this->setItemPriceFormatted($product, $this->price);
            $this->setItemRendered($product, array('item' => $product), $options);
            $products = array($product);
        }

        $widget = array(
            'multiple' => false,
            'products' => $products,
            'name' => 'review[product_id]',
            'error' => $this->error('product_id'),
            'label' => $this->text('Product')
        );

        $this->setData('product_picker', $this->getWidgetProductPicker($widget));
    }

    /**
     * Sets title on the edit review page
     */
    protected function setTitleEditReview()
    {
        if (isset($this->data_review['review_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->text('Review')));
        } else {
            $title = $this->text('Add review');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit review page
     */
    protected function setBreadcrumbEditReview()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Reviews'),
            'url' => $this->url('admin/content/review')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit review page
     */
    protected function outputEditReview()
    {
        $this->output('content/review/edit');
    }

}
