<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;
use core\Logger;
use core\models\User;
use core\models\Store;
use core\models\Price;
use core\models\Product;
use core\models\Currency;
use core\models\Bookmark;
use core\models\Language;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Request;

class Cart
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Hook model instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Request model instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Config model instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     * @param Product $product
     * @param Price $price
     * @param Currency $currency
     * @param User $user
     * @param Bookmark $bookmark
     * @param Language $language
     * @param Store $store
     * @param Hook $hook
     * @param Request $request
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(Product $product, Price $price, Currency $currency, User $user, Bookmark $bookmark, Language $language, Store $store, Hook $hook, Request $request, Config $config, Logger $logger)
    {
        $this->hook = $hook;
        $this->user = $user;
        $this->store = $store;
        $this->price = $price;
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $config->db();
        $this->product = $product;
        $this->request = $request;
        $this->currency = $currency;
        $this->bookmark = $bookmark;
        $this->language = $language;
    }

    /**
     * Returns a cart content for a given user ID
     * @param mixed $user_id
     * @param boolean $cached
     * @return array
     */
    public function getByUser($user_id = null, $cached = true)
    {
        if (!isset($user_id)) {
            $user_id = $this->uid();
        }

        if ($cached) {
            $cart = &Cache::memory("cart.$user_id");

            if (isset($cart)) {
                return $cart;
            }

            $cache = Cache::get("cart.$user_id");

            if (isset($cache)) {
                $cart = $cache;
                return $cart;
            }
        }

        $products = $this->getList(array('user_id' => $user_id));

        if (empty($products)) {
            return array();
        }

        $total = 0;
        $quantity = 0;
        $current_currency = $this->currency->get();

        $cart = array();
        foreach ($products as $cart_id => $item) {
            $item['product'] = $this->product->getBySku($item['sku'], $item['store_id']);

            // Invalid / disabled product
            if (empty($item['product']['status'])) {
                continue;
            }

            // Product store changed
            if ((int) $this->store->id() !== (int) $item['product']['store_id']) {
                continue;
            }

            $price = $item['product']['price'];
            $currency = $item['product']['currency'];

            if (empty($item['product']['combination_id'])) {
                $price = $this->currency->convert($price, $currency, $current_currency);
            } elseif (!empty($item['product']['option_file_id'])) {
                $price = $this->currency->convert($item['product']['option_price'], $currency, $current_currency);
            }

            $item['price'] = $price;
            $item['total'] = $item['price'] * $item['quantity'];
            $total += (int) $item['total'];
            $quantity += (int) $item['quantity'];
            $cart['items'][$cart_id] = $item;
        }

        $cart['total'] = $total;
        $cart['quantity'] = $quantity;
        $cart['currency'] = $current_currency;

        $this->hook->fire('get.cart.after', $user_id, $cart);

        if ($cached) {
            Cache::set("cart.$user_id", $cart);
        }

        return $cart;
    }

    /**
     * Returns a cart user ID
     * @return string
     */
    public function uid()
    {
        $user_id = $this->user->id();

        if ($user_id) {
            return (string) $user_id;
        }

        $cookie_name = $this->config->get('user_cookie_name', 'user_id');
        $user_id = $this->request->cookie($cookie_name);

        if ($user_id) {
            return (string) $user_id;
        }

        $user_id = Tool::randomString(6);
        Tool::setCookie($cookie_name, $user_id, $this->config->get('cart_cookie_lifespan', 31536000));
        return (string) $user_id;
    }

    /**
     * Returns an array of cart items
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $data += array('order_id' => 0);
        
        $sql = '
            SELECT *, SUM(quantity) AS quantity
            FROM cart
            WHERE cart_id > 0';
        
        $where = array();
        
        if (isset($data['user_id'])) {
            $sql .= ' AND user_id=?';
            $where[] = $data['user_id'];
        }
        
        if (isset($data['order_id'])) {
            $sql .= ' AND order_id=?';
            $where[] = $data['order_id'];
        }
        
        $sql .= ' GROUP BY sku ORDER BY created DESC';

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        $results = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $item['data'] = unserialize($item['data']);
            $results[$item['cart_id']] = $item;
        }

        return $results;
    }

    /**
     * Adds a product to the cart
     * @param array $data
     * @return boolean|string
     * Returns true on success,
     * false - needs more data (redirect to product page),
     * string - last validation error
     */
    public function submit(array $data)
    {
        $product = $this->product->get($data['product_id']);

        if (empty($product['status'])) {
            return $this->language->text('An error occurred');
        }

        if (!empty($product['combination'])) {
            return false;
        }

        if (empty($data['quantity'])) {
            $data['quantity'] = 1;
        }

        $result = $this->addProduct($data);

        if ($result === true) {
            return true;
        }

        $error = is_array($result) ? end($result) : $result;
        return $error;
    }

    /**
     * Adds a product to the cart
     * @param array $data
     * @return mixed
     */
    public function addProduct(array $data)
    {
        $this->hook->fire('add.cart.product.before', $data);

        if (empty($data['quantity'])) {
            return false;
        }

        $user_id = $this->uid();
        $product = $this->product->get($data['product_id']);
        $this->validate($data, $product, $user_id);
        $data = array('user_id' => $user_id, 'store_id' => $product['store_id']) + $data;

        $this->hook->fire('presave.cart.product', $data, $product, $this->errors);

        if ($this->errors) {
            return $this->errors;
        }

        $cart_id = $this->setProduct($data, $user_id);
        $this->hook->fire('add.cart.product.after', $data, $cart_id);

        $this->logAddToCart($data, $product, $user_id);
        $this->deleteCache($user_id);
        return true;
    }

    /**
     * Logs adding products to the cart
     * @param array $data
     * @param array $product
     * @param integer|string $user_id
     */
    protected function logAddToCart(array $data, array $product, $user_id)
    {
        $log = array(
            'message' => 'User %uid has added product %product (SKU: %sku) at %store',
            'variables' => array(
                '%uid' => is_numeric($user_id) ? $user_id : '',
                '%product' => $product['product_id'],
                '%sku' => $data['sku'],
                '%store' => $product['store_id']
            )
        );

        $this->logger->log('cart', $log);
    }

    /**
     * Validates a product before adding to the cart
     * @param array $data
     * @param array $product
     * @param string|integer $user_id
     * @return boolean
     */
    protected function validate(array &$data, array $product, $user_id)
    {
        if (!$this->validateProduct($product)) {
            return false;
        }

        if (empty($data['options'])) {
            $data['sku'] = $product['sku'];
            $data['stock'] = $product['stock'];
        } else {
            $data['combination_id'] = $this->product->getCombinationId($data['options'], $product['product_id']);

            if (!empty($product['combination'][$data['combination_id']]['sku'])) {
                $data['sku'] = $product['combination'][$data['combination_id']]['sku'];
                $data['stock'] = $product['combination'][$data['combination_id']]['stock'];
            }
        }

        if (!$this->validateSku($data)) {
            return false;
        }

        if (!$this->validateLimits($data, $product, $user_id)) {
            return false;
        }

        return true;
    }

    /**
     * Validates a product before adding to the cart
     * @param array $product
     * @return boolean
     */
    protected function validateProduct(array $product)
    {
        if (!empty($product['status'])) {
            return true;
        }

        $this->errors[] = $this->language->text('Invalid product');
        return false;
    }

    /**
     * Validates a product SKU before addingto the cart
     * @param array $data
     * @return boolean
     */
    protected function validateSku(array $data)
    {
        if (empty($data['sku'])) {
            $this->errors[] = $this->language->text('SKU not found');
            return false;
        }

        return true;
    }

    /**
     * Validates cart limits for the current cart
     * @param array $data
     * @param array $product
     * @param string|integer $user_id
     * @return boolean
     */
    protected function validateLimits(array $data, array $product, $user_id)
    {
        $total = (int) $data['quantity'];
        $skus = array($data['sku'] => true);

        foreach ($this->getList(array('user_id' => $user_id)) as $item) {
            $skus[$item['sku']] = true;
            $total += (int) $item['quantity'];
        }

        $limit_sku = (int) $this->config->get('cart_sku_limit', 10);
        $limit_total = (int) $this->config->get('cart_total_limit', 20);

        if (!empty($limit_sku) && (count($skus) > $limit_sku)) {
            $this->errors[] = $this->language->text('Sorry, you cannot have more than %num items per SKU in your cart', array(
                '%num' => $limit_sku));
        }

        if (!empty($limit_total) && ($total > $limit_total)) {
            $this->errors[] = $this->language->text('Sorry, you cannot have more than %num items in your cart', array(
                '%num' => $limit_total));
        }

        if ($product['subtract'] && ((int) $data['quantity'] > (int) $data['stock'])) {
            $this->errors[] = $this->language->text('Too low stock level');
        }

        return empty($this->errors);
    }

    /**
     * Adds/updates products in the cart
     * @param array $data
     * @param string|integer $user_id
     * @return integer
     */
    protected function setProduct(array $data, $user_id)
    {
        $sql = 'SELECT cart_id, quantity  FROM cart WHERE sku=:sku AND user_id=:user_id AND order_id=:order_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':sku' => $data['sku'], ':user_id' => $user_id, 'order_id' => 0));
        $existing = $sth->fetch(PDO::FETCH_ASSOC);

        if (isset($existing['cart_id'])) {
            $cart_id = $existing['cart_id'];
            $this->update($cart_id, array('quantity' => $existing['quantity'] ++));
            return $cart_id;
        }

        return $this->add($data);
    }

    /**
     * Updates a cart
     * @param integer $cart_id
     * @param array $data
     * @return boolean
     */
    public function update($cart_id, array $data)
    {
        $this->hook->fire('update.cart.before', $cart_id, $data);

        if (empty($cart_id)) {
            return false;
        }

        $values = array(
            'modified' => isset($data['modified']) ? (int) $data['modified'] : GC_TIME
        );

        if (isset($data['created'])) {
            $values['created'] = (int) $data['created'];
        }

        if (isset($data['user_id'])) {
            $values['user_id'] = $data['user_id'];
        }

        if (isset($data['store_id'])) {
            $values['store_id'] = (int) $data['store_id'];
        }

        if (isset($data['order_id'])) {
            $values['order_id'] = (int) $data['order_id'];
        }

        if (isset($data['quantity'])) {
            $values['quantity'] = (int) $data['quantity'];
        }

        if (isset($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        $result = false;

        if (!empty($values)) {
            $result = $this->db->update('cart', $values, array('cart_id' => $cart_id));
            $cart = $this->get($cart_id);
            $this->deleteCache($cart['user_id']);
            $this->hook->fire('update.cart.after', $cart_id, $data, $result);
        }

        return (bool) $result;
    }

    /**
     * Loads a cart from the database
     * @param integer $cart_id
     * @return array
     */
    public function get($cart_id)
    {
        $sql = 'SELECT * FROM cart WHERE cart_id=:cart_id';
        $where = array(':cart_id' => (int) $cart_id);

        $sth = $this->db->prepare($sql);
        $sth->execute($where);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Clears up cached cart content for a given user
     * @param string $user_id
     */
    public function deleteCache($user_id)
    {
        Cache::clear("cart.$user_id");
    }

    /**
     * Adds a cart record to the database
     * @param type $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.cart.before', $data);

        if (empty($data)) {
            return false;
        }

        $cart_id = $this->db->insert('cart', array(
            'modified' => 0,
            'sku' => $data['sku'],
            'user_id' => $data['user_id'],
            'quantity' => (int) $data['quantity'],
            'store_id' => isset($data['store_id']) ? (int) $data['store_id'] : $this->config->get('store', 1),
            'product_id' => (int) $data['product_id'],
            'order_id' => isset($data['order_id']) ? (int) $data['order_id'] : 0,
            'created' => !empty($data['created']) ? (int) $data['created'] : GC_TIME,
            'data' => isset($data['data']) ? serialize((array) $data['data']) : serialize(array())
        ));

        $this->hook->fire('add.cart.after', $data, $cart_id);
        return $cart_id;
    }

    /**
     * Moves a cart item to the wishlist
     * @param string $sku
     * @param integer $user_id
     * @return mixed
     */
    public function moveToWishlist($sku, $user_id = null)
    {
        $this->hook->fire('move.cart.wishlist.before', $sku, $user_id);

        if (empty($sku)) {
            return false;
        }

        if (!isset($user_id)) {
            $user_id = $this->uid();
        }

        $sth = $this->db->prepare('SELECT product_sku_id FROM product_sku WHERE sku=:sku');
        $sth->execute(array(':sku' => $sku));

        $product_sku_id = $sth->fetchColumn();

        if (empty($product_sku_id)) {
            return false;
        }

        $this->db->delete('bookmark', array(
            'id_key' => 'product_sku_id',
            'id_value' => (int) $product_sku_id
        ));

        $bookmark_id = $this->bookmark->add(array(
            'id_key' => 'product_sku_id',
            'id_value' => (int) $product_sku_id,
            'user_id' => $user_id
        ));

        $this->db->delete('cart', array('sku' => $sku, 'user_id' => $user_id));
        $this->deleteCache($user_id);
        $this->hook->fire('move.cart.wishlist.after', $sku, $user_id, $bookmark_id);

        return $bookmark_id;
    }

    /**
     * Deletes a cart record from the database
     * @param integer $cart_id
     * @param string $user_id
     * @param integer $order_id
     * @return boolean
     */
    public function delete($cart_id, $user_id = null, $order_id = 0)
    {
        $arguments = func_get_args();

        $this->hook->fire('delete.cart.before', $arguments);

        if (empty($arguments)) {
            return false;
        }

        if ($user_id) {
            $this->deleteCache($user_id);
            // Cart orders with order_id = 0 are not linked to orders, i.e before checkout
            $where = array('user_id' => $user_id, 'order_id' => (int) $order_id);
        }

        $cart = $this->get($cart_id);

        if ($cart) {
            $this->deleteCache($cart['user_id']);
            $where = array('cart_id' => (int) $cart_id);
        }

        if (empty($where)) {
            return false;
        }

        $result = $this->db->delete('cart', $where);
        $this->hook->fire('delete.cart.after', $arguments, $result);
        return (bool) $result;
    }

    /**
     * Deletes a cart from the cookie
     * @return boolean
     */
    public function deleteCookie()
    {
        $cookie_name = $this->config->get('user_cookie_name', 'user_id');
        return Tool::deleteCookie($cookie_name);
    }

    /**
     * Performs all needed tastks when customer logged in during checkout
     * @param array $user
     * @param array $cart
     */
    public function login($user, $cart)
    {
        $this->hook->fire('cart.login.before', $user, $cart);

        if (empty($user) || empty($cart)) {
            return false;
        }

        $log = array(
            'message' => 'User has logged in during checkout using %email',
            'variables' => array('%email' => $user['email'])
        );

        $this->logger->log('checkout', $log);

        if (!$this->config->get('cart_login_merge', 0)) {
            $this->delete(false, $user['user_id']);
        }

        foreach ($cart['items'] as $item) {
            $this->update($item['cart_id'], array('user_id' => $user['user_id']));
        }

        $this->deleteCookie();

        $this->hook->fire('cart.login.after', $user, $cart);
        return true;
    }
}
