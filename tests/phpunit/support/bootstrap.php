<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
ini_set('error_reporting', E_ALL);
ini_set('memory_limit', '-1');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once __DIR__ . '/../../../system/bootstrap.php';

if (is_file(GC_DIR . '/composer.lock') && is_file(GC_DIR . '/vendor/autoload.php')) {
    require_once GC_DIR . '/vendor/autoload.php';
}

if (!class_exists('PHPUnit_Extensions_Database_TestCase')) {
    throw new \LogicException('Looks like DBUnit extension not installed. See https://github.com/sebastianbergmann/dbunit');
}
