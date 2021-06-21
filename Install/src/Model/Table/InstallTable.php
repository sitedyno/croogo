<?php

namespace Croogo\Install\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\File;

class InstallTable extends Table
{

    /**
     * name
     *
     * @var string
     */
    public $name = 'Install';

    /**
     * useTable
     *
     * @var string
     */
    public $useTable = false;

    /**
     *
     * @var CroogoPlugin
     */
    protected $_CroogoPlugin = null;

    /**
     * Create admin user
     *
     * @var array $user User datas
     * @return If user is created
     */
    public function addAdminUser($user)
    {
        $Users = TableRegistry::get('Croogo/Users.Users');
        $Users->removeBehavior('Cached');
        $Roles = TableRegistry::get('Croogo/Users.Roles');
        $Roles->addBehavior('Croogo/Core.Aliasable');
        $Users->getValidator('default')->remove('email')->remove('password');
        $user['name'] = $user['username'];
        $user['email'] = '';
        $user['timezone'] = 'UTC';
        $user['role_id'] = $Roles->byAlias('superadmin');
        $user['status'] = true;
        $user['activation_key'] = md5(uniqid());
        $entity = $Users->get(1);
        $entity = $Users->patchEntity($entity, $user);
        if ($entity->getErrors()) {
            $this->err('Unable to create administrative user. Validation errors:');

            return $this->err($entity->getErrors());
        }
        $saved = $Users->save($entity);

        return $saved;
    }
}
