<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers;

use core\Controller;
use core\models\Cart;
use core\models\Price;
use core\models\Image;
use core\models\Product;
use core\models\Bookmark;
use core\models\Category;
use core\models\Search as S;

class Search extends Controller
{

    /**
     * Cart model instance
     * @var \core\models\Cart $cart
     */
    protected $cart;

    /**
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Constructor
     * @param Bookmark $bookmark
     * @param Product $product
     * @param S $search
     * @param Cart $cart
     * @param Price $price
     * @param Image $image
     * @param Category $category
     */
    public function __construct(Bookmark $bookmark, Product $product, S $search, Cart $cart, Price $price, Image $image, Category $category)
    {
        parent::__construct();

        $this->cart = $cart;
        $this->price = $price;
        $this->image = $image;
        $this->search = $search;
        $this->product = $product;
        $this->category = $category;
        $this->bookmark = $bookmark;
    }

    public function search()
    {
        $term = $this->request->get('q', '');

        $view = $this->config->module($this->theme, 'catalog_view', 'grid');
        $sort = $this->config->module($this->theme, 'catalog_sort', 'price');
        $order = $this->config->module($this->theme, 'catalog_order', 'asc');

        $default = array('sort' => $sort, 'order' => $order, 'view' => $view);

        $query = $this->getFilterQuery($default);
        $total = $this->getTotalResults($term);
        $limit = $this->setPager($total, $query, $this->config->module($this->theme, 'catalog_limit', 20));
        $products = $this->getResults($term, $limit, $query);

        $this->data['results'] = $this->getRenderedResults($products);
        $this->data['navbar'] = $this->getRenderedNavbar(count($products), $total, $query);

        $this->setBlockCategoryMenu();
        $this->setBlockRecentProducts();

        $this->setTitleSearch($term);
        $this->setBreadcrumbSearch();
        $this->outputSearch();
    }

    /**
     * Sets titles on the search page
     * @param string $term
     */
    protected function setTitleSearch($term)
    {
        if ($term) {
            $title = $this->text('Search for <small>%term</small>', array('%term' => $term));
        } else {
            $title = $this->text('Search');
        }

        $this->setTitle($title);
    }

    /**
     * Renders the search page templates
     */
    protected function outputSearch()
    {
        $this->output('search/search');
    }

    /**
     * Sets breadcrumbs on the search page
     * @param array $category
     */
    protected function setBreadcrumbSearch()
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
        $this->setBreadcrumb(array('text' => $this->text('Search')));
    }

    /**
     * Returns a total number of results found
     * @param string $term
     * @return integer
     */
    protected function getTotalResults($term)
    {
        $total = $this->search->search('product_id', $term, array(
            'count' => true,
            'status' => 1,
            'store_id' => $this->store_id,
            'language' => $this->langcode));

        return (int) $total;
    }

    /**
     * Returns an array of search results
     * @param string $term
     * @param integer $limit
     * @param array $query
     * @return array
     */
    protected function getResults($term, $limit, array $query = array())
    {
        $options = array(
            'status' => 1,
            'language' => $this->langcode,
            'store_id' => $this->store_id,
            'limit' => $limit) + $query;

        $results = $this->search->search('product_id', $term, $options);
        return $this->prepareProducts($results, $query);
    }

    /**
     * Prepares an array of search results before rendering
     * @param array $products
     * @param array $query
     * @return array
     */
    protected function prepareProducts($products, array $query)
    {
        $user_id = $this->cart->uid();
        $product_ids = array_keys($products);
        $pricerules = $this->store->config('catalog_pricerule');
        $view = in_array($query['view'], array('list', 'grid')) ? $query['view'] : 'grid';
        $imagestyle = $this->config->module($this->theme, "image_style_product_$view", 3);

        foreach ($products as $product_id => &$product) {
            $product['in_comparison'] = $this->product->isCompared($product_id);
            $product['in_wishlist'] = $this->bookmark->exists($product_id, array('user_id' => $user_id, 'type' => 'product'));
            $product['thumb'] = $this->image->getThumb($product_id, $imagestyle, 'product_id', $product_ids);
            $product['url'] = $product['alias'] ? $this->url($product['alias']) : $this->url("product/$product_id");

            if ($pricerules) {
                $calculated = $this->product->calculate($product, $this->store_id);
                $product['price'] = $calculated['total'];
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
            $product['rendered'] = $this->render("product/item/$view", array(
                'product' => $product,
                'buttons' => array('cart_add', 'wishlist_add', 'compare_add')));
        }
        return $products;
    }

    /**
     * Returns a string containing ready-to-display products
     * @param array $products
     * @return string
     */
    protected function getRenderedResults($products)
    {
        return $this->render('product/list', array('products' => $products));
    }

    /**
     * Returns ready-to-display category navbar
     * @param integer $quantity
     * @param integer $total
     * @return string
     */
    protected function getRenderedNavbar($quantity, $total, $query)
    {
        $options = array(
            'total' => $total,
            'quantity' => $quantity,
            'view' => $query['view'],
            'sort' => "{$query['sort']}-{$query['order']}"
        );

        return $this->render('category/navbar', $options);
    }

    /**
     * Sets sidebar menu
     */
    protected function setBlockCategoryMenu()
    {
        $this->addRegionItem('region_left', array('category/block/menu', array(
                'tree' => $this->getCategoryTree())));
    }

    /**
     * Returns an array of categories
     * @return array
     */
    protected function getCategoryTree()
    {
        $options = array(
            'status' => 1,
            'store_id' => $this->store_id,
            'type' => 'catalog',
        );

        $tree = $this->category->getTree($options);
        return $this->prepareCategoryTree($tree);
    }

    /**
     * Modifies an array of categories before rendering
     * @param array $tree
     * @return array
     */
    protected function prepareCategoryTree($tree)
    {
        foreach ($tree as &$item) {
            $item['url'] = $item['alias'] ? $item['alias'] : "category/{$item['category_id']}";
            $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
        }

        return $tree;
    }

    /**
     * Adds recently viewed products block
     */
    protected function setBlockRecentProducts()
    {
        $this->addRegionItem('region_bottom', array(
            'product/block/recent', array(
                'products' => $this->getRecentProducts())));
    }

    /**
     * Returns an array of recently viewed products
     * @return array
     */
    protected function getRecentProducts()
    {
        $limit = $this->config->get('product_recent_limit', 12);
        $product_ids = $this->product->getViewed($limit);

        if (empty($product_ids)) {
            return array();
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));
        return $this->prepareProducts($products, array('view' => 'grid'));
    }
}
