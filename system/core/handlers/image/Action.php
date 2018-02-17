<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\image;

use Exception;
use gplcart\core\helpers\Image;

/**
 * Methods to process images
 */
class Action
{

    /**
     * Image helper class instance
     * @var \gplcart\core\helpers\Image $image
     */
    protected $image;

    /**
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Make thumbnail
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function thumbnail(&$source, $target, array $action)
    {
        try {
            list($width, $height) = $action['value'];
            $this->image->set($source)->thumbnail($width, $height)->save($target);
            $source = $target;
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Crop
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function crop(&$source, $target, array $action)
    {
        try {
            list($x1, $y1, $x2, $y2) = $action['value'];
            $this->image->set($source)->crop($x1, $y1, $x2, $y2)->save($target);
            $source = $target;
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Resize
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function resize(&$source, $target, array $action)
    {
        try {
            list($width, $height) = $action['value'];
            $this->image->set($source)->resize($width, $height)->save($target);
            $source = $target;
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Fit to width
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function fitWidth(&$source, $target, array $action)
    {
        try {
            $width = reset($action['value']);
            $this->image->set($source)->fitToWidth($width)->save($target);
            $source = $target;
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Fit to height
     * @param string $source
     * @param string $target
     * @param array $action
     * @return bool
     */
    public function fitHeight(&$source, $target, array $action)
    {
        try {
            $height = reset($action['value']);
            $this->image->set($source)->fitToHeight($height)->save($target);
            $source = $target;
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

}
