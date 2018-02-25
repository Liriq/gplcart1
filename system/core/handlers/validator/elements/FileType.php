<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\elements;

/**
 * Provides methods to validate file types
 */
class FileType
{

    /**
     * Whether the file is an image
     * @param string $file
     * @return boolean
     */
    public function image($file)
    {
        return strpos(mime_content_type($file), 'image/') === 0;
    }

    /**
     * Whether the file is a CSV file
     * @param string $file
     * @return boolean
     */
    public function csv($file)
    {
        return strpos(mime_content_type($file), 'text/') === 0;
    }

    /**
     * Whether the file is a ZIP file
     * @param string $file
     * @return boolean
     */
    public function zip($file)
    {
        if (!function_exists('zip_open')) {
            return false;
        }

        $zip = zip_open($file);

        if (is_resource($zip)) {
            zip_close($zip);
            return true;
        }

        return false;
    }

    /**
     * Whether the file is a .json file
     * @param string $file
     * @return boolean
     */
    public function json($file)
    {
        $contents = file_get_contents($file);
        json_decode($contents);
        return json_last_error() === JSON_ERROR_NONE;
    }

}
