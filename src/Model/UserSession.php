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
use Phire\Sessions\Table;

/**
 * User Session Model class
 *
 * @category   Phire\Sessions
 * @package    Phire\Sessions
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class UserSession extends AbstractModel
{

    /**
     * Get all user sessions
     *
     * @param  int    $roleId
     * @param  array  $search
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($roleId = null, array $search = null, $limit = null, $page = null, $sort = null)
    {
        $sql = Table\UserSessions::sql();
        $sql->select([
            'id'           => DB_PREFIX . 'user_sessions.id',
            'user_id'      => DB_PREFIX . 'user_sessions.user_id',
            'ip'           => DB_PREFIX . 'user_sessions.ip',
            'ua'           => DB_PREFIX . 'user_sessions.ua',
            'start'        => DB_PREFIX . 'user_sessions.start',
            'user_role_id' => DB_PREFIX . 'users.role_id',
            'username'     => DB_PREFIX . 'users.username',
            'first_name'   => DB_PREFIX . 'users.first_name',
            'last_name'    => DB_PREFIX . 'users.last_name',
            'company'      => DB_PREFIX . 'users.company',
            'title'        => DB_PREFIX . 'users.title',
            'email'        => DB_PREFIX . 'users.email',
            'active'       => DB_PREFIX . 'users.active',
            'verified'     => DB_PREFIX . 'users.verified',
            'role_id'      => DB_PREFIX . 'roles.id',
            'role_name'    => DB_PREFIX . 'roles.name'
        ])->join(DB_PREFIX . 'users', [DB_PREFIX . 'users.id' => DB_PREFIX . 'user_sessions.user_id'])
          ->join(DB_PREFIX . 'roles', [DB_PREFIX . 'users.role_id' => DB_PREFIX . 'roles.id']);

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $sql->select()->offset($page)->limit($limit);
        }

        $params = [];
        $order  = $this->getSortOrder($sort, $page);
        $by     = explode(' ', $order);
        $sql->select()->orderBy($by[0], $by[1]);

        if (null !== $search) {
            $sql->select()->where($search['by'] . ' LIKE :' . $search['by']);
            $params[$search['by']] = $search['for'] . '%';
        }

        if (null !== $roleId) {
            if ($roleId == 0) {
                $sql->select()->where(DB_PREFIX . 'users.role_id IS NULL');
                $rows = (count($params) > 0) ?
                    Table\UserSessions::execute((string)$sql, $params)->rows() :
                    Table\UserSessions::query((string)$sql)->rows();
            } else {
                $sql->select()->where(DB_PREFIX . 'users.role_id = :role_id');
                $params['role_id'] = $roleId;
                $rows = Table\UserSessions::execute((string)$sql, $params)->rows();
            }
        } else {
            $rows = (count($params) > 0) ?
                Table\UserSessions::execute((string)$sql, $params)->rows() :
                Table\UserSessions::query((string)$sql)->rows();
        }

        return $rows;
    }

    /**
     * Get user session data
     *
     * @param  int $id
     * @return array
     */
    public function getUserData($id)
    {
        $user     = \Phire\Table\Users::findById($id);
        $userData = Table\UserSessionData::findById($id);

        if (isset($userData->user_id)) {
            $data = $userData->getColumns();
            if (null !== $data['logins']) {
                $this->data['logins'] = unserialize($data['logins']);
                krsort($this->data['logins']);
            }
            $this->data['total_logins']    = (int)$data['total_logins'];
            $this->data['failed_attempts'] = $data['failed_attempts'];
        } else {
            $this->data['logins']          = [];
            $this->data['total_logins']    = 0;
            $this->data['failed_attempts'] = 0;
        }

        $this->data['username'] = $user->username;
        $this->data['user_id']  = $id;
    }

    /**
     * Remove a user session
     *
     * @param  array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['rm_sessions'])) {
            foreach ($post['rm_sessions'] as $id) {
                $session = Table\UserSessions::findById((int)$id);
                if (isset($session->id)) {
                    $session->delete();
                }
            }
        }
    }

    /**
     * Clear user data
     *
     * @param  array $post
     * @return void
     */
    public function clear(array $post)
    {
        if (isset($post['user_id'])) {
            $session = Table\UserSessionData::findById((int)$post['user_id']);
            if (isset($session->user_id)) {
                if (isset($post['clear_logins'])) {
                    $session->logins       = null;
                    $session->total_logins = 0;
                }
                if (isset($post['clear_failed_attempts'])) {
                    $session->failed_attempts = 0;
                }
                $session->save();
            }
        }
    }

    /**
     * Determine if list of user sessions have pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\UserSessions::findAll()->count() > $limit);
    }

    /**
     * Get count of user sessions
     *
     * @return int
     */
    public function getCount()
    {
        return Table\UserSessions::findAll()->count();
    }

}
