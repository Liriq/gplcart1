<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Library;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\File as FileModel;

/**
 * Manages basic behaviors and data related to images
 */
class Image extends Model
{

    /**
     * File model instance
     * @var \gplcart\core\models\File $file;
     */
    protected $file;

    /**
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Library class instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * Constructor
     * @param FileModel $file
     * @param UrlHelper $url
     * @param Library $library
     */
    public function __construct(FileModel $file, UrlHelper $url,
            Library $library)
    {
        parent::__construct();

        $this->url = $url;
        $this->file = $file;
        $this->library = $library;
    }

    /**
     * Returns a string containing an image url
     * @param array $data
     * @param array $options
     * @return string
     */
    public function getThumb(array $data, array $options)
    {
        if (empty($options['ids'])) {
            return empty($options['placeholder']) ? '' : $this->placeholder($options['imagestyle']);
        }

        $conditions = array(
            'order' => 'asc',
            'sort' => 'weight',
            'file_type' => 'image',
            'id_value' => $options['ids'],
            'id_key' => $options['id_key']
        );

        foreach ((array) $this->file->getList($conditions) as $file) {
            if ($file['id_value'] == $data[$options['id_key']]) {
                return $this->url($options['imagestyle'], $file['path']);
            }
        }

        return empty($options['placeholder']) ? '' : $this->placeholder($options['imagestyle']);
    }

    /**
     * Deletes multiple images
     * @param array $options
     * @return bool
     */
    public function deleteMultiple(array $options)
    {
        return $this->file->deleteMultiple($options);
    }

    /**
     * Modify an image (crop, watermark etc)
     * @param string $file
     * @param array $actions
     */
    public function modify($file, array $actions = array())
    {
        $this->library->load('simpleimage');

        $applied = 0;

        try {

            $object = new \abeautifulsite\SimpleImage($file);

            foreach ($actions as $action_id => $action) {
                if ($this->validateAction($file, $action_id, $action)) {
                    $applied++;
                    call_user_func_array(array($object, $action_id), (array) $action['value']);
                }
            }
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
            return 0;
        }

        return $applied;
    }

    /**
     * Returns an array of image style names
     * @return array
     */
    public function getStyleNames()
    {
        $names = array();
        foreach ($this->getStyleList() as $imagestyle_id => $imagestyle) {
            $names[$imagestyle_id] = $imagestyle['name'];
        }

        return $names;
    }

    /**
     * Returns an array of image styles
     * @return array
     */
    public function getStyleList()
    {
        $default_imagestyles = $this->defaultStyles();
        $saved_imagestyles = $this->config->get('imagestyles', array());

        $imagestyles = gplcart_array_merge($default_imagestyles, $saved_imagestyles);

        foreach ($imagestyles as $imagestyle_id => &$imagestyle) {
            $imagestyle['imagestyle_id'] = $imagestyle_id;
            $imagestyle['default'] = isset($default_imagestyles[$imagestyle_id]);
        }

        $this->hook->fire('imagestyle.list', $imagestyles);

        return $imagestyles;
    }

    /**
     * Returns an array of imagestyle actions
     * @param integer $imagestyle_id
     * @return array
     */
    public function getStyleActions($imagestyle_id)
    {
        $styles = $this->getStyleList();

        if (empty($styles[$imagestyle_id]['actions'])) {
            return array();
        }

        $actions = $styles[$imagestyle_id]['actions'];

        gplcart_array_sort($actions);
        return $actions;
    }

    /**
     * Loads an image style
     * @param  integer $imagestyle_id
     * @return array
     */
    public function getStyle($imagestyle_id)
    {
        $imagestyles = $this->getStyleList();
        return isset($imagestyles[$imagestyle_id]) ? $imagestyles[$imagestyle_id] : array();
    }

    /**
     * Adds an imagestyle
     * @param array $data
     * @return integer
     */
    public function addStyle(array $data)
    {
        $this->hook->fire('imagestyle.add.before', $data);

        $imagestyles = $this->getStyleList();
        $imagestyle_id = $imagestyles ? (int) max(array_keys($imagestyles)) : 0;
        $imagestyle_id++;

        $allowed = array('name', 'status', 'actions');
        $imagestyles[$imagestyle_id] = array_intersect_key($data, array_flip($allowed));

        $this->config->set('imagestyles', $imagestyles);

        $this->hook->fire('imagestyle.add.after', $data, $imagestyle_id);

        return $imagestyle_id;
    }

    /**
     * Updates an imagestyle
     * @param integer $imagestyle_id
     * @param array $data
     * @return boolean
     */
    public function updateStyle($imagestyle_id, array $data)
    {
        $this->hook->fire('imagestyle.update.before', $imagestyle_id, $data);

        $imagestyles = $this->getStyleList();

        if (empty($imagestyles[$imagestyle_id])) {
            return false;
        }

        $allowed = array('name', 'status', 'actions');
        $imagestyles[$imagestyle_id] = array_intersect_key($data, array_flip($allowed));

        $this->config->set('imagestyles', $imagestyles);

        $this->hook->fire('imagestyle.update.after', $imagestyle_id, $data);
        return true;
    }

    /**
     * Deletes an imagestyle
     * @param integer $imagestyle_id
     * @return boolean
     */
    public function deleteStyle($imagestyle_id)
    {
        $this->hook->fire('imagestyle.delete.before', $imagestyle_id);

        $imagestyles = $this->getStyleList();

        if (empty($imagestyles[$imagestyle_id])) {
            return false;
        }

        unset($imagestyles[$imagestyle_id]);

        $this->config->set('imagestyles', $imagestyles);
        $this->hook->fire('imagestyle.delete.after', $imagestyle_id);
        return true;
    }

    /**
     * Removes cached files for a given imagestyle
     * @param integer|null $imagestyle_id
     * @return boolean
     */
    public function clearCache($imagestyle_id = null)
    {
        $this->hook->fire('imagestyle.clear.cache.before', $imagestyle_id);

        $directory = GC_IMAGE_CACHE_DIR;

        if (!empty($imagestyle_id)) {
            $directory = "$directory/$imagestyle_id";
        }

        $result = gplcart_file_delete_recursive($directory);
        $this->hook->fire('imagestyle.clear.cache.after', $imagestyle_id, $result);
        return $result;
    }

    /**
     * Returns a string containing image placeholder URL
     * @param integer|null $imagestyle_id
     * @param boolean $absolute
     * @return string
     */
    public function placeholder($imagestyle_id = null, $absolute = false)
    {
        $placeholder = $this->config->get('no_image', 'image/misc/no-image.png');

        if (isset($imagestyle_id)) {
            return $this->url($imagestyle_id, $placeholder, $absolute);
        }

        return $this->url->get($placeholder, array(), true);
    }

    /**
     * Returns a string containing an image cache URL
     * @param integer $imagestyle_id
     * @param string $image
     * @param boolean $absolute
     * @return string
     */
    public function url($imagestyle_id, $image, $absolute = false)
    {
        if (empty($image)) {
            return $this->placeholder($imagestyle_id, $absolute);
        }

        $image = trim($image, "/");

        $file = GC_IMAGE_CACHE_DIR . "/$imagestyle_id/" . preg_replace('/^image\//', '', $image);
        $options = file_exists($file) ? array('v' => filemtime($file)) : array('v' => GC_TIME);
        $path = "files/image/cache/$imagestyle_id/$image";

        return $this->url->get($path, $options, $absolute);
    }

    /**
     * Makes a relative to the root directory URL from the server path
     * @param string $path
     * @return string
     */
    public function urlFromPath($path)
    {
        return $this->url->get('files/' . gplcart_relative_path($path), array(
                    'v' => filemtime(GC_FILE_DIR . "/$path")));
    }

    /**
     * Returns true if the action is valid
     * @param string $file
     * @param integer $action_id
     * @param array $action
     * @return boolean
     */
    protected function validateAction($file, $action_id, array &$action)
    {
        if ($action_id == 'overlay') {

            $action['value'][0] = GC_FILE_DIR . '/' . $action['value'][0];
            $overlay_pathinfo = pathinfo($action['value'][0]);
            $fileinfo = pathinfo($file);

            if ($overlay_pathinfo['extension'] != $fileinfo['extension']) {
                $action['value'][0] = GC_FILE_DIR . "/{$overlay_pathinfo['filename']}.{$fileinfo['extension']}";
            }

            if (!file_exists($action['value'][0])) {
                return false;
            }
        }

        if ($action_id == 'text') {
            $action['value'][1] = GC_FILE_DIR . '/' . $action['value'][1];
            if (!file_exists($action['value'][1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns default image styles
     * @return array
     */
    protected function defaultStyles()
    {
        $styles = array();

        $styles[1] = array(
            'name' => '50X50',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(50, 50),
                ),
            ),
        );

        $styles[2] = array(
            'name' => '100X100',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(100, 100),
                ),
            ),
        );

        $styles[3] = array(
            'name' => '150X150',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(150, 150),
                ),
            ),
        );

        $styles[4] = array(
            'name' => '200X200',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(200, 200),
                ),
            ),
        );

        $styles[5] = array(
            'name' => '300X300',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(300, 300),
                ),
            ),
        );

        $styles[6] = array(
            'name' => '400X400',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(400, 400),
                ),
            ),
        );

        $styles[7] = array(
            'name' => '1140X400',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(1140, 380),
                ),
            ),
        );

        return $styles;
    }

}
