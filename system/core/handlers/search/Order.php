<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\search;

use PDO;
use core\Config;
use core\models\Price;
use core\models\Search;

class Order
{

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
     * Queue model instance
     * @var \core\models\Queue $queue
     */
    protected $queue;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Search $search
     * @param Price $price
     * @param Config $config
     */
    public function __construct(Search $search, Price $price, Config $config)
    {
        $this->search = $search;
        $this->price = $price;
        $this->config = $config;
        $this->db = $config->db();
    }

    /**
     * Returns an array of suggested orders for a given query
     * @param string $query
     * @param array $options
     * @return array
     */
    public function search($query, $options)
    {
        $orders= array();
        return $orders;
    }
}
