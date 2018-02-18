<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

/**
 * Handles incoming requests and outputs data related to user shopping carts
 */
class Cart extends Controller
{

    /**
     * Pager limits
     * @var array
     */
    protected $data_limit;

    /**
     * Displays the shopping cart overview page
     */
    public function listCart()
    {
        $this->actionListCart();
        $this->setTitleListCart();
        $this->setBreadcrumbListCart();
        $this->setFilterListCart();
        $this->setPagerlListCart();

        $this->setData('carts', $this->getListCart());
        $this->outputListCart();
    }

    /**
     * Set the current filter query on the cart overview page
     */
    protected function setFilterListCart()
    {
        $allowed = array('user_email', 'user_id', 'store_id', 'sku',
            'order_id', 'created', 'modified', 'quantity', 'sku_like');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected shopping cart items
     */
    protected function actionListCart()
    {
        list($selected, $action) = $this->getPostedAction();

        $deleted = 0;
        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('cart_delete')) {
                $deleted += (int) $this->cart->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerlListCart()
    {
        $options = $this->query_filter;
        $options['count'] = true;
        $total = (int) $this->cart->getList($options);

        $pager = array(
            'total' => $total,
            'query' => $this->query_filter
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of shopping cart items
     * @return array
     */
    protected function getListCart()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        $list = (array) $this->cart->getList($conditions);
        $this->prepareListCart($list);
        return $list;
    }

    /**
     * Prepare an array of cart items
     * @param array $items
     */
    protected function prepareListCart(array &$items)
    {
        foreach ($items as &$item) {
            $this->setItemUrlEntity($item, $this->store, 'product');
        }
    }

    /**
     * Sets title on the cart overview page
     */
    protected function setTitleListCart()
    {
        $this->setTitle($this->text('Shopping cart items'));
    }

    /**
     * Sets breadcrumbs on the cart overview page
     */
    protected function setBreadcrumbListCart()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the cart overview page
     */
    protected function outputListCart()
    {
        $this->output('sale/cart/list');
    }

}
