<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
require 'system/bootstrap.php';

/* @var $facade \gplcart\core\Facade */
$facade = \gplcart\core\Container::get('gplcart\\core\\Facade');
$facade->routeHttp();

