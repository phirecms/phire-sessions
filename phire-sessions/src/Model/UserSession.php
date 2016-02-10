<?php

namespace Phire\Sessions\Model;

use Phire\Model\AbstractModel;
use Phire\Sessions\Table;

class UserSession extends AbstractModel
{

    /**
     * Get all user sessions
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return array
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $sql = Table\UserSessions::sql();
        $sql->select([
            'id'           => DB_PREFIX . 'user_sessions.id',
            'user_id'      => DB_PREFIX . 'user_sessions.user_id',
            'ip'           => DB_PREFIX . 'user_sessions.ip',
            'ua'           => DB_PREFIX . 'user_sessions.ua',
            'start'        => DB_PREFIX . 'user_sessions.start',
            'username'     => DB_PREFIX . 'users.username',
            'role_id'      => DB_PREFIX . 'users.role_id',
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

        return (count($params) > 0) ?
            Table\UserSessions::execute((string)$sql, $params)->rows() :
            Table\UserSessions::query((string)$sql)->rows();
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
            $this->data['failed_attempts'] = $data['failed_attempts'];
        } else {
            $this->data['logins']          = [];
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
                    $session->logins = null;
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
