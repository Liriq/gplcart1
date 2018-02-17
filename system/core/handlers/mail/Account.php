<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail;

/**
 * Mail handlers related to user accounts
 */
class Account extends Base
{
    /**
     * Sent to an admin when a user has been registered
     * @param array $user
     * @return array
     */
    public function registeredToAdmin(array $user)
    {
        $store = $this->store->get($user['store_id']);

        $vars = array(
            '@name' => $user['name'],
            '@store' => $store['name'],
            '@email' => $user['email'],
            '@user_id' => $user['user_id'],
            '@status' => empty($user['status']) ? $this->translation->text('Inactive') : $this->translation->text('Active')
        );

        $subject = $this->translation->text('New account on @store', $vars);
        $message = $this->translation->text("A new account has been created on @store\r\n\r\nE-mail: @email\r\nName: @name\r\nUser ID: @user_id\r\nStatus: @status", $vars);

        $options = array('from' => $this->store->getConfig('email.0', $store));
        return array($options['from'], $subject, $message, $options);
    }

    /**
     * Sent to the user when his account has been created
     * @param array $user
     * @return array
     */
    public function registeredToCustomer(array $user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->getConfig(null, $store);
        $store_name = $this->store->getTranslation('title', $this->translation->getLangcode(), $store);
        $base = $this->store->getUrl($store);

        $vars = array(
            '@store' => $store_name,
            '@name' => $user['name'],
            '@order' => "$base/account/{$user['user_id']}",
            '@edit' => "$base/account/{$user['user_id']}/edit",
            '@status' => empty($user['status']) ? $this->translation->text('Inactive') : $this->translation->text('Active')
        );

        $subject = $this->translation->text('Account details for @name on @store', $vars);
        $message = $this->translation->text("Thank you for registering on @store\r\n\r\nAccount status: @status\r\n\r\nEdit account: @edit\r\nView orders: @order", $vars);
        $message .= $this->getSignature($options);

        $options['from'] = $this->store->getConfig('email.0', $store);
        return array($user['email'], $subject, $message, $options);
    }

    /**
     * Sent when a user wants to reset his password
     * @param array $user
     * @return array
     */
    public function resetPassword(array $user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->getConfig(null, $store);
        $store_name = $this->store->getTranslation('title', $this->translation->getLangcode(), $store);
        $base = $this->store->getUrl($store);

        $date_format = $this->config->get('date_full_format', 'd.m.Y H:i');

        $vars = array(
            '@name' => $user['name'],
            '@store' => $store_name,
            '@expires' => date($date_format, $user['data']['reset_password']['expires']),
            '@link' => "$base/forgot?" . http_build_query(array('key' => $user['data']['reset_password']['token'], 'user_id' => $user['user_id'])),
        );

        $subject = $this->translation->text('Password recovery for @name on @store', $vars);
        $message = $this->translation->text("You or someone else requested a new password on @store\r\n\r\nTo get the password please click on the following link:\r\n@link\r\n\r\nThis link expires on @expires and nothing will happen if it's not used", $vars);
        $message .= $this->getSignature($options);

        $options['from'] = $this->store->getConfig('email.0', $store);
        return array($user['email'], $subject, $message, $options);
    }

    /**
     * Sent to the user whose password has been changed
     * @param array $user
     * @return array
     */
    public function changedPassword(array $user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->getConfig(null, $store);
        $store_name = $this->store->getTranslation('title', $this->translation->getLangcode(), $store);

        $vars = array('@store' => $store_name, '@name' => $user['name']);
        $subject = $this->translation->text('Password has been changed for @name on @store', $vars);

        $message = $this->translation->text('Your password on @store has been changed', $vars);
        $message .= $this->getSignature($options);

        $options['from'] = $this->store->getConfig('email.0', $store);
        return array($user['email'], $subject, $message, $options);
    }

}
