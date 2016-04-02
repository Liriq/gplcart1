<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Page as P;
use core\models\Category;
use core\models\Alias;
use core\models\Image;

class Page extends Controller
{

    /**
     * Page model instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Constructor
     * @param P $page
     * @param Category $category
     * @param Alias $alias
     * @param Image $image
     */
    public function __construct(P $page, Category $category, Alias $alias, Image $image)
    {
        parent::__construct();

        $this->page = $page;
        $this->category = $category;
        $this->alias = $alias;
        $this->image = $image;
    }

    /**
     * Displays the page overview page
     */
    public function pages()
    {
        $action = $this->request->post('action');
        $selected = $this->request->post('selected', array());
        $value = $this->request->post('value');

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalPages($query), $query);

        $this->data['pages'] = $this->getPages($total, $query);
        $this->data['stores'] = $this->store->getNames();

        $filters = array('title', 'store_id', 'status', 'created', 'email', 'front');
        $this->setFilter($filters, $query);

        if ($this->request->post('save')) {
            $this->submit();
        }

        $this->setTitlePages();
        $this->setBreadcrumbPages();
        $this->outputPages();
    }

    /**
     * Returns number of total pages for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalPages($query)
    {
        return $this->page->getList(array('count' => true) + $query);
    }

    /**
     * Sets titles on the page overview page
     */
    protected function setTitlePages()
    {
        $this->setTitle($this->text('Pages'));
    }

    /**
     * Sets breadcrumbs on the page overview page
     */
    protected function setBreadcrumbPages()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders templates for the pages overview page
     */
    protected function outputPages()
    {
        $this->output('content/page/list');
    }

    /**
     * Applies an action to the selected pages
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        $deleted = $updated = 0;
        foreach ($selected as $page_id) {
            if ($action == 'status' && $this->access('page_edit')) {
                $updated += (int) $this->page->update($page_id, array('status' => $value));
            }
            
            if ($action == 'front' && $this->access('page_edit')) {
                $updated += (int) $this->page->update($page_id, array('front' => $value));
            }

            if ($action == 'delete' && $this->access('page_delete')) {
                $deleted += (int) $this->page->delete($page_id);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Pages have been updated'), 'success');
            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Pages have been deleted'), 'success');
            return true;
        }

        return false;
    }

    /**
     * Displays the page edit form
     * @param integer|null $page_id
     */
    public function edit($page_id = null)
    {
        $page = $this->get($page_id);

        if ($this->request->post('action') == 'categories') {
            $store_id = $this->request->post('store_id', $this->store->getDefault());
            $this->response->json($this->category->getOptionListByStore($store_id));
        }

        if ($this->request->post('delete')) {
            $this->delete($page);
        }

        $this->data['page'] = $page;
        $this->data['stores'] = $this->store->getNames();

        if ($this->request->post('save')) {
            $this->submit($page);
        }

        $this->preparePage();
        $this->setTitleEdit($page);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Sets titles on the page edit page
     */
    protected function setTitleEdit($page)
    {
        $title = $this->text('Add page');

        if (isset($page['page_id'])) {
            $title = $this->text('Edit page %title', array('%title' => $page['title']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the page edit page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Pages'), 'url' => $this->url('admin/content/page')));
    }

    /**
     * Renders the page edit templates
     */
    protected function outputEdit()
    {
        $this->output('content/page/edit');
    }

    /**
     * Modifies a page data
     * @return null
     */
    protected function preparePage()
    {
        $store_id = $this->store->getDefault();
        if (isset($this->data['page']['store_id'])) {
            $store_id = $this->data['page']['store_id'];
        }

        $this->data['categories'] = $this->category->getOptionListByStore($store_id);

        if (empty($this->data['page']['images'])) {
            return;
        }

        foreach ($this->data['page']['images'] as &$image) {
            $image['thumb'] = $this->image->url($this->config->get('admin_image_preset', 2), $image['path']);
            $image['uploaded'] = filemtime(GC_FILE_DIR . '/' . $image['path']);
        }

        $this->data['attached_images'] = $this->render('common/image/attache', array(
            'name_prefix' => 'page',
            'languages' => $this->languages,
            'images' => $this->data['page']['images'])
        );
    }

    /**
     * Returns a page
     * @param integer $page_id
     * @return array
     */
    protected function get($page_id)
    {
        if (!is_numeric($page_id)) {
            return array();
        }

        $page = $this->page->get($page_id);

        if (!$page) {
            $this->outputError(404);
        }

        $user = $this->user->get($page['user_id']);
        $page['author'] = $user['email'];
        $page['alias'] = $this->alias->get('page_id', $page['page_id']);

        if (empty($page['images'])) {
            return $page;
        }

        foreach ($page['images'] as &$image) {
            $image['translation'] = $this->image->getTranslations($image['file_id']);
        }

        return $page;
    }

    /**
     * Returns an array of pages
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getPages($limit, $query)
    {
        $stores = $this->store->getList();
        $pages = $this->page->getList(array('limit' => $limit) + $query);

        foreach ($pages as &$page) {
            $page['url'] = '';
            if (isset($stores[$page['store_id']])) {
                $store = $stores[$page['store_id']];
                $page['url'] = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", "/") . "/page/{$page['page_id']}";
            }
        }

        return $pages;
    }

    /**
     * Deletes a page
     * @param array $page
     */
    protected function delete($page)
    {
        $this->controlAccess('page_delete');
        $this->page->delete($page['page_id']);
        $this->redirect('admin/content/page', $this->text('Page has been deleted'), 'success');
    }

    /**
     * Saves a submitted page
     * @param array $page
     * @return null
     */
    protected function submit($page = array())
    {
        $images = $this->request->post('delete_image');

        if ($images && ($this->access('page_add') || $this->access('page_edit'))) {
            foreach ($images as $file_id) {
                $this->image->delete($file_id);
            }
        }

        $this->submitted = $this->request->post('page', array(), false);
        $this->validate($page);

        if ($this->formErrors()) {
            $this->data['page'] = $this->submitted;
            return;
        }

        if (isset($page['page_id'])) {
            $this->controlAccess('page_edit');
            $this->page->update($page['page_id'], $this->submitted);
            $this->redirect('admin/content/page', $this->text('Page has been updated'), 'success');
        }

        $this->controlAccess('page_add');
        $this->submitted += array('user_id' => $this->uid);
        $this->page->add($this->submitted);
        $this->redirect('admin/content/page', $this->text('Page has been added'), 'success');
    }

    /**
     * Validates a single page
     * @param array $page
     * @return array|boolean
     */
    protected function validate($page = array())
    {
        $this->validateAlias($page);
        $this->validateTitle();
        $this->validateDescription();
        $this->validateMetaTitle();
        $this->validateMetaDescription();
        $this->validateTranslation();
        $this->validateImages();

        $this->submitted['status'] = !empty($this->submitted['status']);
    }

    /**
     * Validates alias field
     * @param array $page
     * @return boolean
     */
    protected function validateAlias($page)
    {
        if (empty($this->submitted['alias'])) {
            if (isset($page['page_id'])) {
                $this->submitted['alias'] = $this->page->generateAlias($page);
                return true;
            }
            return true;
        }

        $check_alias = true;
        if (isset($page['alias'])) {
            $check_alias = ($page['alias'] !== $this->submitted['alias']);
        }

        if ($check_alias && $this->alias->exists($this->submitted['alias'])) {
            $this->data['form_errors']['alias'] = $this->text('URL alias already exists');
            return false;
        }

        return true;
    }

    /**
     * Validates title field
     * @return boolean
     */
    protected function validateTitle()
    {
        if (!isset($this->submitted['title'])) {
            return true;
        }

        if (!$this->submitted['title'] || mb_strlen($this->submitted['title']) > 255) {
            $this->data['form_errors']['title'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates description field
     * @return boolean
     */
    protected function validateDescription()
    {
        if (isset($this->submitted['description']) && !$this->submitted['description']) {
            $this->data['form_errors']['description'] = $this->text('Required field');
            return false;
        }
        return true;
    }

    /**
     * Validates meta title field
     * @return boolean
     */
    protected function validateMetaTitle()
    {
        if (isset($this->submitted['meta_title']) && mb_strlen($this->submitted['meta_title']) > 255) {
            $this->data['form_errors']['meta_title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates meta description field
     * @return boolean
     */
    protected function validateMetaDescription()
    {
        if (isset($this->submitted['meta_description']) && mb_strlen($this->submitted['meta_description']) > 255) {
            $this->data['form_errors']['meta_description'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates page translation fields
     * @return boolean
     */
    protected function validateTranslation()
    {
        if (empty($this->submitted['translation'])) {
            return false;
        }

        $has_errors = false;
        foreach ((array) $this->submitted['translation'] as $lang => &$translation) {
            if (mb_strlen($translation['title']) > 255) {
                $this->data['form_errors']['translation'][$lang]['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }

            if (mb_strlen($translation['meta_title']) > 255) {
                $this->data['form_errors']['translation'][$lang]['meta_title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }

            if (mb_strlen($translation['meta_description']) > 255) {
                $this->data['form_errors']['translation'][$lang]['meta_description'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates submitted images
     * @return boolean
     */
    protected function validateImages()
    {
        if (empty($this->submitted['images'])) {
            return true;
        }

        foreach ($this->submitted['images'] as &$image) {
            if (empty($image['title']) && isset($this->submitted['title'])) {
                $image['title'] = $this->submitted['title'];
            }

            if (empty($image['description']) && isset($this->submitted['title'])) {
                $image['description'] = $this->submitted['title'];
            }

            $image['title'] = $this->truncate($image['title'], 255);

            if (empty($image['translation'])) {
                continue;
            }

            foreach ($image['translation'] as &$translation) {
                $translation['title'] = $this->truncate($translation['title'], 255);
            }
        }

        return true;
    }
}
