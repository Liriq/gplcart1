<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Collection as CollectionModel;
use gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to collection items
 */
class CollectionItem extends BackendController
{

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection
     */
    protected $collection_item;

    /**
     * The current collection
     * @var array
     */
    protected $data_collection = array();

    /**
     * Constructor
     * @param CollectionModel $collection
     * @param CollectionItemModel $collection_item
     */
    public function __construct(CollectionModel $collection,
            CollectionItemModel $collection_item)
    {
        parent::__construct();

        $this->collection = $collection;
        $this->collection_item = $collection_item;
    }

    /**
     * Displays the collection items overview page
     * @param integer $collection_id
     */
    public function listCollectionItem($collection_id)
    {
        $this->setCollectionCollectionItem($collection_id);

        $this->actionCollectionItem();

        $this->setTitleListCollectionItem();
        $this->setBreadcrumbListCollectionItem();

        $this->setData('collection', $this->data_collection);
        $this->setData('items', $this->getListCollectionItem());

        $this->outputListCollectionItem();
    }

    /**
     * Returns an collection
     * @param integer $collection_id
     * @return array
     */
    protected function setCollectionCollectionItem($collection_id)
    {
        if (!is_numeric($collection_id)) {
            return array();
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection)) {
            $this->outputHttpStatus(404);
        }

        $this->data_collection = $collection;
        return $collection;
    }

    /**
     * Applies an action to the selected collections
     * @return null
     */
    protected function actionCollectionItem()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        if ($action === 'weight' && $this->access('collection_item_edit')) {
            $this->updateWeightCollectionItem($selected);
            return null;
        }

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('collection_item_edit')) {
                $updated += (int) $this->collection_item->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('collection_item_delete')) {
                $deleted += (int) $this->collection_item->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Collection items have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Collection items have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Updates weight of collection items
     * @param array $items
     */
    protected function updateWeightCollectionItem(array $items)
    {
        foreach ($items as $id => $weight) {
            $this->collection_item->update($id, array('weight' => $weight));
        }

        $response = array(
            'success' => $this->text('Collection items have been reordered'));

        $this->response->json($response);
    }

    /**
     * Returns an array of collection items
     * @return array
     */
    protected function getListCollectionItem()
    {
        $conditions = array(
            'type' => $this->data_collection['type'],
            'collection_id' => $this->data_collection['collection_id']
        );

        return $this->collection_item->getItems($conditions);
    }

    /**
     * Sets title on the collection items page
     */
    protected function setTitleListCollectionItem()
    {
        $vars = array('%name' => $this->data_collection['title']);
        $title = $this->text('Items of collection %name', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the collection items page
     */
    protected function setBreadcrumbListCollectionItem()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Collections'),
            'url' => $this->url('admin/content/collection')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the collection items page
     */
    protected function outputListCollectionItem()
    {
        $this->output('content/collection/item/list');
    }

    /**
     * Displays add collection item form
     * @param integer $collection_id
     */
    public function editCollectionItem($collection_id)
    {
        $this->setCollectionCollectionItem($collection_id);

        $this->setTitleEditCollectionItem();
        $this->setBreadcrumbEditCollectionItem();

        $this->setData('collection', $this->data_collection);
        $this->setData('handler', $this->getHandlerCollectionItem());
        $this->setData('weight', $this->collection_item->getNextWeight($collection_id));

        $this->submitCollectionItem();

        $this->setJsEditCollectionItem();
        $this->outputEditCollectionItem();
    }

    /**
     * Returns an array of handler data for the collection type
     * @return array
     */
    protected function getHandlerCollectionItem()
    {
        $handlers = $this->collection->getHandlers();
        $type = $this->data_collection['type'];

        if (empty($handlers[$type])) {
            $this->outputHttpStatus(403);
        }

        return $handlers[$type];
    }

    /**
     * Saves a submitted collection item
     * @return null
     */
    protected function submitCollectionItem()
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('collection_item');
        $this->validateCollectionItem();

        if (!$this->hasErrors('collection_item')) {
            $this->addCollectionItem();
        }
    }

    /**
     * Validates a submitted collection item
     */
    protected function validateCollectionItem()
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('collection_id', $this->data_collection['collection_id']);
        $this->validate('collection_item');
    }

    /**
     * Adds a new item to the collection
     */
    protected function addCollectionItem()
    {
        $this->controlAccess('collection_item_add');

        $added = $this->collection_item->add($this->getSubmitted());

        if (empty($added)) {
            $message = $this->text('Collection item has not been added');
            $this->redirect('', $message, 'warning');
        }

        $url = "admin/content/collection-item/{$this->data_collection['collection_id']}";
        $message = $this->text('Collection item has been added');
        $this->redirect($url, $message, 'success');
    }

    /**
     * Sets JS on the edit collection item page
     */
    protected function setJsEditCollectionItem()
    {
        $this->setJsSettings('collection', $this->data_collection);
    }

    /**
     * Sets title on the collection items page
     */
    protected function setTitleEditCollectionItem()
    {
        $vars = array('%name' => $this->data_collection['title']);
        $title = $this->text('Add item to collection %name', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the collection items page
     */
    protected function setBreadcrumbEditCollectionItem()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/collection'),
            'text' => $this->text('Collections')
        );

        $breadcrumbs[] = array(
            'url' => $this->url("admin/content/collection-item/{$this->data_collection['collection_id']}"),
            'text' => $this->text('Items of collection %name', array('%name' => $this->data_collection['title']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the collection items page
     */
    protected function outputEditCollectionItem()
    {
        $this->output('content/collection/item/edit');
    }

}
