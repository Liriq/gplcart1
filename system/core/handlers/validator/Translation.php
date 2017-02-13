<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\File as FileModel;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate translations
 */
class Translation extends BaseValidator
{

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param FileModel $file
     * @param RequestHelper $request
     */
    public function __construct(FileModel $file, RequestHelper $request)
    {
        parent::__construct();

        $this->file = $file;
        $this->request = $request;
    }

    /**
     * Validates a uploaded translation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function upload(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateLanguageTranslation();
        $this->validateUploadTranslation();

        return $this->getResult();
    }

    /**
     * Validates translation language code
     */
    protected function validateLanguageTranslation()
    {
        $code = $this->getSubmitted('language');

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Language'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('language', $error);
            return false;
        }

        $language = $this->language->get($code);

        if (empty($language)) {
            $vars = array('@name' => $this->language->text('Language'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('language', $error);
            return false;
        }
    }

    /**
     * Uploads and validates a translation
     * @return boolean
     */
    protected function validateUploadTranslation()
    {
        if ($this->isError()) {
            return null;
        }

        $file = $this->request->file('file');

        if (empty($file)) {
            $vars = array('@field' => $this->language->text('File'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('file', $error);
            return false;
        }

        $code = $this->getSubmitted('language');

        $result = $this->file->setUploadPath(GC_LOCALE_DIR . "/$code")
                ->setHandler('csv')
                ->upload($file);

        if ($result !== true) {
            $this->setError('file', (string) $result);
            return false;
        }

        $uploaded = $this->file->getUploadedFile();
        $this->setSubmitted('destination', $uploaded);
        return true;
    }

}