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
namespace Phire\Sessions\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Session Config Form class
 *
 * @category   Phire\Sessions
 * @package    Phire\Sessions
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class SessionConfig extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  int    $id
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return SessionConfig
     */
    public function __construct($id = null, array $fields, $action = null, $method = 'post')
    {
        $configs = \Phire\Sessions\Table\UserSessionConfig::findAll();
        $configsAry = [];

        foreach ($configs->rows() as $config) {
            $configsAry[] = $config->role_id;
        }

        $roles = Table\Roles::findAll();
        $roleValues = ['----' => '----'];
        foreach ($roles->rows() as $role) {
            if (!in_array($role->id, $configsAry) || ($id == $role->id)) {
                $roleValues[$role->id] = $role->name;
            }
        }

        $fields[0]['role_id']['value'] = $roleValues;

        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'session-form');
        $this->setIndent('    ');
    }

}