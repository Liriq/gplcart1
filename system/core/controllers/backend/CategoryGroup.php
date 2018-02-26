<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;

/**
 * Handles incoming requests and outputs data related to category groups
 */
class CategoryGroup extends Controller
{

    /**
     * Entity translation model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current category group
     * @var array
     */
    protected $data_category_group = array();

    /**
     * @param CategoryGroupModel $category_group
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(CategoryGroupModel $category_group, TranslationEntityModel $translation_entity)
    {
        parent::__construct();

        $this->category_group = $category_group;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Displays the category group overview page
     */
    public function listCategoryGroup()
    {
        $this->setTitleListCategoryGroup();
        $this->setBreadcrumbListCategoryGroup();
        $this->setFilterListCategoryGroup();
        $this->setPagerListCategoryGroup();

        $this->setData('category_groups', $this->getListCategoryGroup());
        $this->setData('category_group_types', $this->category_group->getTypes());

        $this->outputListCategoryGroup();
    }

    /**
     * Sets the current filter parameters
     */
    protected function setFilterListCategoryGroup()
    {
        $allowed = array('title', 'store_id', 'type', 'category_group_id');
        $this->setFilter($allowed);
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListCategoryGroup()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->category_group->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of category groups
     * @return array
     */
    protected function getListCategoryGroup()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        return $this->category_group->getList($conditions);
    }

    /**
     * Sets page title on the category group overview page
     */
    protected function setTitleListCategoryGroup()
    {
        $this->setTitle($this->text('Category groups'));
    }

    /**
     * Sets breadcrumbs on the category group overview page
     */
    protected function setBreadcrumbListCategoryGroup()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the category group overview page
     */
    protected function outputListCategoryGroup()
    {
        $this->output('content/category/group/list');
    }

    /**
     * Displays the edit category group page
     * @param integer|null $category_group_id
     */
    public function editCategoryGroup($category_group_id = null)
    {
        $this->setCategoryGroup($category_group_id);
        $this->setTitleEditCategoryGroup();
        $this->setBreadcrumbEditCategoryGroup();

        $this->setData('category_group', $this->data_category_group);
        $this->setData('can_delete', $this->canDeleteCategoryGroup());
        $this->setData('languages', $this->language->getList(array('in_database' => true)));
        $this->setData('category_group_types', $this->category_group->getTypes());

        $this->submitEditCategoryGroup();
        $this->outputEditCategoryGroup();
    }

    /**
     * Whether the category group can be deleted
     * @return boolean
     */
    protected function canDeleteCategoryGroup()
    {
        return isset($this->data_category_group['category_group_id'])
            && $this->category_group->canDelete($this->data_category_group['category_group_id'])
            && $this->access('category_group_delete');
    }

    /**
     * Sets the category group data
     * @param integer $category_group_id
     */
    protected function setCategoryGroup($category_group_id)
    {
        $this->data_category_group = array();

        if (is_numeric($category_group_id)) {

            $conditions = array(
                'language' => 'und',
                'category_group_id' => $category_group_id
            );

            $this->data_category_group = $this->category_group->get($conditions);

            if (empty($this->data_category_group)) {
                $this->outputHttpStatus(404);
            }

            $this->prepareCategoryGroup($this->data_category_group);
        }
    }

    /**
     * Prepare an array of category group data
     * @param array $category_group
     */
    protected function prepareCategoryGroup(array &$category_group)
    {
        $this->setItemTranslation($category_group, 'category_group', $this->translation_entity);
    }

    /**
     * Handles a submitted category group data
     */
    protected function submitEditCategoryGroup()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCategoryGroup();
        } else if ($this->isPosted('save') && $this->validateEditCategoryGroup()) {
            if (isset($this->data_category_group['category_group_id'])) {
                $this->updateCategoryGroup();
            } else {
                $this->addCategoryGroup();
            }
        }
    }

    /**
     * Validates a submitted category group data
     */
    protected function validateEditCategoryGroup()
    {
        $this->setSubmitted('category_group');
        $this->setSubmitted('update', $this->data_category_group);

        $this->validateComponent('category_group');

        return !$this->hasErrors(false);
    }

    /**
     * Deletes a category group
     */
    protected function deleteCategoryGroup()
    {
        $this->controlAccess('category_group_delete');

        if ($this->category_group->delete($this->data_category_group['category_group_id'])) {
            $this->redirect('admin/content/category-group', $this->text('Category group has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Category group has not been deleted'), 'warning');
    }

    /**
     * Updates a category group
     */
    protected function updateCategoryGroup()
    {
        $this->controlAccess('category_group_edit');

        if ($this->category_group->update($this->data_category_group['category_group_id'], $this->getSubmitted())) {
            $this->redirect('admin/content/category-group', $this->text('Category group has been updated'), 'success');
        }

        $this->redirect('', $this->text('Category group has not been updated'), 'warning');
    }

    /**
     * Adds a new category group
     */
    protected function addCategoryGroup()
    {
        $this->controlAccess('category_group_add');

        if ($this->category_group->add($this->getSubmitted())) {
            $this->redirect('admin/content/category-group', $this->text('Category group has been added'), 'success');
        }

        $this->redirect('', $this->text('Category group has not been added'), 'warning');
    }

    /**
     * Sets titles on the category group edit page
     */
    protected function setTitleEditCategoryGroup()
    {
        if (isset($this->data_category_group['category_group_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_category_group['title']));
        } else {
            $title = $this->text('Add category group');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit category group page
     */
    protected function setBreadcrumbEditCategoryGroup()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/category-group'),
            'text' => $this->text('Category groups')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the category group edit page
     */
    protected function outputEditCategoryGroup()
    {
        $this->output('content/category/group/edit');
    }

}
