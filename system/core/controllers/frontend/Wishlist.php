<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

/**
 * Handles incoming requests and outputs data related to user wishlists
 */
class Wishlist extends Controller
{

    /**
     * Displays the wishlist page
     */
    public function indexWishlist()
    {
        $this->setTitleIndexWishlist();
        $this->setBreadcrumbIndexWishlist();
        $this->setDataIndexWishlist();
        $this->outputIndexWishlist();
    }

    /**
     * Sets titles on the wishlist page
     */
    protected function setTitleIndexWishlist()
    {
        $this->setTitle($this->text('My wishlist'));
    }

    /**
     * Sets breadcrumbs on the wishlist page
     */
    protected function setBreadcrumbIndexWishlist()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the wishlist page
     */
    protected function outputIndexWishlist()
    {
        $this->output('wishlist');
    }

    /**
     * Returns an array of wishlist items for the current user
     * @return array
     */
    protected function getProductsWishlist()
    {
        $list = $this->getWishlist();

        if (empty($list)) {
            return array();
        }

        $ids = array();

        foreach ((array) $list as $result) {
            $ids[] = $result['product_id'];
        }

        $conditions = array('product_id' => $ids);

        $options = array(
            'buttons' => array(
                'cart_add', 'wishlist_remove', 'compare_add')
        );

        return $this->getProducts($conditions, $options);
    }

    /**
     * Sets rendered product list
     */
    protected function setDataIndexWishlist()
    {
        $data = array('products' => $this->getProductsWishlist());
        $this->setData('products', $this->render('product/list', $data));
    }

}
