<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\UserRole as UserRoleModel;

/**
 * Handles incoming requests and outputs data related to users
 */
class User extends Controller
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * A n array of user data
     * @var array
     */
    protected $data_user = array();

    /**
     * @param UserRoleModel $role
     */
    public function __construct(UserRoleModel $role)
    {
        parent::__construct();

        $this->role = $role;
    }

    /**
     * Displays the users overview page
     */
    public function listUser()
    {
        $this->actionListUser();
        $this->setTitleListUser();
        $this->setBreadcrumbListUser();
        $this->setFilterListUser();
        $this->setPagerListUser();

        $this->setData('roles', $this->role->getList());
        $this->setData('users', $this->getListUser());

        $this->outputListUser();
    }

    /**
     * Set filter on the user overview page
     */
    protected function setFilterListUser()
    {
        $allowed = array('name', 'email', 'email_like', 'role_id', 'store_id',
            'status', 'created', 'user_id');

        $this->setFilter($allowed);
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListUser()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->user->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Applies an action to the selected users
     */
    protected function actionListUser()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $deleted = $updated = 0;

        foreach ($selected as $uid) {

            if ($this->isSuperadmin($uid)) {
                continue;
            }

            if ($action === 'status' && $this->access('user_edit')) {
                $updated += (int) $this->user->update($uid, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('user_delete')) {
                $deleted += (int) $this->user->delete($uid);
            }
        }

        if ($updated > 0) {
            $text = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($text, 'success');
        }

        if ($deleted > 0) {
            $text = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($text, 'success');
        }
    }

    /**
     * Returns an array of users
     * @return array
     */
    protected function getListUser()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        $list = (array) $this->user->getList($conditions);
        $this->prepareListUser($list);
        return $list;
    }

    /**
     * Prepare an array of users
     * @param array $list
     */
    protected function prepareListUser(array &$list)
    {
        $stores = $this->store->getList();

        foreach ($list as &$item) {

            $item['url'] = '';

            if (isset($stores[$item['store_id']])) {
                $item['url'] = $this->store->getUrl($stores[$item['store_id']]) . "/account/{$item['user_id']}";
            }
        }
    }

    /**
     * Sets title on the user overview page
     */
    protected function setTitleListUser()
    {
        $this->setTitle($this->text('Users'));
    }

    /**
     * Sets breadcrumbs on the user overview page
     */
    protected function setBreadcrumbListUser()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the user overview page
     */
    protected function outputListUser()
    {
        $this->output('user/list');
    }

    /**
     * Displays the user edit page
     * @param integer|null $user_id
     */
    public function editUser($user_id = null)
    {
        $this->setUser($user_id);
        $this->setTitleEditUser();
        $this->setBreadcrumbEditUser();
        $this->controlAccessEditUser($user_id);

        $this->setData('user', $this->data_user);
        $this->setData('roles', $this->role->getList());
        $this->setData('can_delete', $this->canDeleteUser());
        $this->setData('is_superadmin', $this->isSuperadminUser());
        $this->setData('password_limit', $this->user->getPasswordLength());

        $this->submitEditUser();
        $this->outputEditUser();
    }

    /**
     * Whether the user can be deleted
     * @return boolean
     */
    protected function canDeleteUser()
    {
        return isset($this->data_user['user_id'])
            && $this->access('user_delete')
            && $this->user->canDelete($this->data_user['user_id']);
    }

    /**
     * Whether the user is superadmin
     * @return bool
     */
    protected function isSuperadminUser()
    {
        return isset($this->data_user['user_id']) && $this->isSuperadmin($this->data_user['user_id']);
    }

    /**
     * Sets a user data
     * @param integer|null $user_id
     */
    protected function setUser($user_id)
    {
        $this->data_user = array();

        if (is_numeric($user_id)) {

            $this->data_user = $this->user->get($user_id);

            if (empty($this->data_user)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Controls access for the given user ID
     * @param integer $user_id
     */
    protected function controlAccessEditUser($user_id)
    {
        if ($this->isSuperadmin($user_id) && !$this->isSuperadmin()) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Handles a submitted user data
     */
    protected function submitEditUser()
    {
        if ($this->isPosted('delete')) {
            $this->deleteUser();
        } else if ($this->isPosted('save') && $this->validateEditUser()) {
            if (isset($this->data_user['user_id'])) {
                $this->updateUser();
            } else {
                $this->addUser();
            }
        }
    }

    /**
     * Validates a submitted user data
     * @return bool
     */
    protected function validateEditUser()
    {
        $this->setSubmitted('user');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_user);

        $this->validateComponent('user', array('admin' => $this->access('user_edit')));

        return !$this->hasErrors();
    }

    /**
     * Deletes a user
     */
    protected function deleteUser()
    {
        $this->controlAccess('user_delete');

        if ($this->user->delete($this->data_user['user_id'])) {
            $this->redirect('admin/user/list', $this->text('User has been deleted'), 'success');
        }

        $this->redirect('', $this->text('User has not been deleted'), 'warning');
    }

    /**
     * Updates a user
     */
    protected function updateUser()
    {
        $this->controlAccess('user_edit');

        if ($this->user->update($this->data_user['user_id'], $this->getSubmitted())) {
            $this->redirect('admin/user/list', $this->text('User has been updated'), 'success');
        }

        $this->redirect('', $this->text('User has not been updated'), 'warning');
    }

    /**
     * Adds a new user
     */
    protected function addUser()
    {
        $this->controlAccess('user_add');

        if ($this->user->add($this->getSubmitted())) {
            $this->redirect('admin/user/list', $this->text('User has been added'), 'success');
        }

        $this->redirect('', $this->text('User has not been added'), 'warning');
    }

    /**
     * Sets title on the edit user page
     */
    protected function setTitleEditUser()
    {
        if (isset($this->data_user['name'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_user['name']));
        } else {
            $title = $this->text('Add user');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit user page
     */
    protected function setBreadcrumbEditUser()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Users'),
            'url' => $this->url('admin/user/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit user page
     */
    protected function outputEditUser()
    {
        $this->output('user/edit');
    }

}
