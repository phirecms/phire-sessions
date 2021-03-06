<?php
/**
 * Phire Sessions Module
 *
 * @link       https://github.com/phirecms/phire-sessions
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Sessions\Model;

use Phire\Model\AbstractModel;
use Pop\Http\Response;
use Phire\Sessions\Table;

/**
 * Session Config Model class
 *
 * @category   Phire\Sessions
 * @package    Phire\Sessions
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class SessionConfig extends AbstractModel
{

    /**
     * Get all session config
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $sql = Table\UserSessionConfig::sql();
        $sql->select([
            'role_id'             => DB_PREFIX . 'user_session_config.role_id',
            'multiple_sessions'   => DB_PREFIX . 'user_session_config.multiple_sessions',
            'allowed_attempts'    => DB_PREFIX . 'user_session_config.allowed_attempts',
            'session_expiration'  => DB_PREFIX . 'user_session_config.session_expiration',
            'timeout_warning'     => DB_PREFIX . 'user_session_config.timeout_warning',
            'ip_allowed'          => DB_PREFIX . 'user_session_config.ip_allowed',
            'ip_blocked'          => DB_PREFIX . 'user_session_config.ip_blocked',
            'log_emails'          => DB_PREFIX . 'user_session_config.log_emails',
            'role'                => DB_PREFIX . 'roles.name'
        ])->join(DB_PREFIX . 'roles', [DB_PREFIX . 'roles.id' => DB_PREFIX . 'user_session_config.role_id']);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $sql->select()->offset($page)->limit($limit);
        }

        $params = [];
        $order  = $this->getSortOrder($sort, $page);
        $by     = explode(' ', $order);
        $sql->select()->orderBy($by[0], $by[1]);

        return (count($params) > 0) ?
            Table\UserSessionConfig::execute((string)$sql, $params)->rows() :
            Table\UserSessionConfig::query((string)$sql)->rows();
    }

    /**
     * Get session config by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $session = Table\UserSessionConfig::findById((int)$id);
        if (isset($session->role_id)) {
            $this->data['role_id']            = $session->role_id;
            $this->data['multiple_sessions']  = $session->multiple_sessions;
            $this->data['allowed_attempts']   = $session->allowed_attempts;
            $this->data['session_expiration'] = ($session->session_expiration > 0) ?
                round($session->session_expiration / 60) : 0;
            $this->data['timeout_warning']    = (int)$session->timeout_warning;
            $this->data['ip_allowed']         = $session->ip_allowed;
            $this->data['ip_blocked']         = $session->ip_blocked;
            $this->data['log_emails']         = $session->log_emails;
            $this->data['log_type']           = $session->log_type;

            $role = \Phire\Table\Roles::findById($session->role_id);
            $this->data['role'] = $role->name;
        }
    }

    /**
     * Save new session config
     *
     * @param  array $fields
     * @return void
     */
    public function save(array $fields)
    {
        $session = new Table\UserSessionConfig([
            'role_id'            => ($fields['role_id'] != '----') ? $fields['role_id'] : null,
            'multiple_sessions'  => (int)$fields['multiple_sessions'],
            'allowed_attempts'   => (int)$fields['allowed_attempts'],
            'session_expiration' => (int)$fields['session_expiration'] * 60,
            'timeout_warning'    => (int)$fields['timeout_warning'],
            'ip_allowed'         => $fields['ip_allowed'],
            'ip_blocked'         => $fields['ip_blocked'],
            'log_emails'         => $fields['log_emails'],
            'log_type'           => ($fields['log_type'] != '--') ? (int)$fields['log_type'] : null,
        ]);
        $session->save();

        $this->data = array_merge($this->data, $session->getColumns());

    }

    /**
     * Update an existing session config
     *
     * @param  array $fields
     * @return void
     */
    public function update(array $fields)
    {
        $session = Table\UserSessionConfig::findById((int)$fields['role_id']);
        if (isset($session->role_id)) {
            $session->role_id            = ($fields['role_id'] != '----') ? $fields['role_id'] : null;
            $session->multiple_sessions  = (int)$fields['multiple_sessions'];
            $session->allowed_attempts   = (int)$fields['allowed_attempts'];
            $session->session_expiration = (int)$fields['session_expiration'] * 60;
            $session->timeout_warning    = (int)$fields['timeout_warning'];
            $session->ip_allowed         = $fields['ip_allowed'];
            $session->ip_blocked         = $fields['ip_blocked'];
            $session->log_emails         = $fields['log_emails'];
            $session->log_type           = ($fields['log_type'] != '--') ? (int)$fields['log_type'] : null;
            $session->save();

            $this->data = array_merge($this->data, $session->getColumns());
        } else {
            $this->save($fields);
        }
    }

    /**
     * Remove a session config
     *
     * @param  array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['rm_sessions'])) {
            foreach ($post['rm_sessions'] as $id) {
                $session = Table\UserSessionConfig::findById((int)$id);
                if (isset($session->role_id)) {
                    $session->delete();
                }
            }
        }
    }

    /**
     * Determine if list of session configs have pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\UserSessionConfig::findAll()->count() > $limit);
    }

    /**
     * Get count of session configs
     *
     * @return int
     */
    public function getCount()
    {
        return Table\UserSessionConfig::findAll()->count();
    }

    /**
     * Determine if roles are available to create configs for
     *
     * @return int
     */
    public function rolesAvailable()
    {
        return (Table\UserSessionConfig::findAll()->count() < \Phire\Table\Roles::findAll()->count());
    }
}
