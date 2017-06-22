<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use gplcart\core\exceptions\AuthorizationException;

/**
 * Provides methods to work with sessions
 */
class Session
{

    /**
     * Constructor
     * @throws AuthorizationException
     */
    public function __construct()
    {
        $this->start();
    }

    /**
     * Init a new session
     * @throws AuthorizationException
     */
    public function start()
    {
        if (!GC_CLI && !$this->started() && !session_start()) {
            throw new AuthorizationException('Failed to start the session');
        }
    }

    /**
     * Returns the current session status
     * @return bool
     */
    public function started()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Regenerates the current session
     * @param boolean $delete_old_session
     */
    public function regenerate($delete_old_session)
    {
        if (!session_regenerate_id($delete_old_session)) {
            throw new AuthorizationException('Failed to regenerate the current session');
        }
    }

    /**
     * Sets a message to be displayed to the user
     * @param string $message
     * @param string $type
     * @return boolean
     */
    public function setMessage($message, $type = 'info')
    {
        if ($message === '') {
            return false;
        }

        $messages = (array) $this->get("messages.$type", array());

        if (in_array($message, $messages)) {
            return false;
        }

        $messages[] = $message;
        return $this->set("messages.$type", $messages);
    }

    /**
     * Returns a session data
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($_SESSION)) {
            $value = gplcart_array_get_value($_SESSION, $key);
        }

        if (isset($value)) {
            return $value;
        }

        return $default;
    }

    /**
     * Saves/updates a data in the session
     * @param string|array $key
     * @param mixed $value
     * @return boolean
     */
    public function set($key, $value = null)
    {
        if (isset($_SESSION)) {
            gplcart_array_set_value($_SESSION, $key, $value);
            return true;
        }
        return false;
    }

    /**
     * Deletes a data from the session
     * @param string|null|array $key
     * @return boolean
     */
    public function delete($key = null)
    {
        if (!$this->started()) {
            return false;
        }

        if (!isset($key)) {
            session_unset();
            if (!session_destroy()) {
                throw new AuthorizationException('Failed to delete the session');
            }

            return true;
        }

        gplcart_array_unset_value($_SESSION, $key);
        return true;
    }

    /**
     * Returns messages from the session
     * @param string $type
     * @return string
     */
    public function getMessage($type = null)
    {
        $key = array('messages');

        if (isset($type)) {
            $key[] = $type;
        }

        $message = $this->get($key, array());
        $this->delete($key);
        return $message;
    }

    /**
     * Sets/gets the session token
     * @param mixed $value
     * @return mixed
     */
    public function token($value = null)
    {
        if (isset($value)) {
            return $this->set('token', $value);
        }

        return $this->get('token');
    }

}
