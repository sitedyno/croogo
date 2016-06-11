<?php

namespace Croogo\Blocks\Controller\Admin;

use Cake\Event\Event;
use Croogo\Blocks\Model\Entity\Block;

/**
 * Blocks Controller
 *
 * @category Blocks.Controller
 * @package  Croogo.Blocks.Controller
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class BlocksController extends AppController
{
    public $paginate = [
        'order' => [
            'region_id' => 'asc',
            'weight' => 'asc',
        ]
    ];

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Croogo/Users.Roles');

        $this->loadCroogoComponents(['BulkProcess']);
        $this->_setupPrg();

        $this->Crud->config('actions.index', [
            'searchFields' => ['region_id', 'title']
        ]);
        $this->Crud->config('actions.moveUp', [
            'className' => 'Croogo/Core.Admin/MoveUp'
        ]);
        $this->Crud->config('actions.moveDown', [
            'className' => 'Croogo/Core.Admin/MoveDown'
        ]);
    }

/**
 * Admin process
 *
 * @return void
 * @access public
 */
    public function process()
    {
        $Blocks = $this->Blocks;
        list($action, $ids) = $this->BulkProcess->getRequestVars($Blocks->alias());

        $options = [
            'messageMap' => [
                'delete' => __d('croogo', 'Blocks deleted'),
                'publish' => __d('croogo', 'Blocks published'),
                'unpublish' => __d('croogo', 'Blocks unpublished'),
                'copy' => __d('croogo', 'Blocks copied'),
            ],
        ];

        return $this->BulkProcess->process($Blocks, $action, $ids, $options);
    }

    public function beforePaginate(Event $event)
    {
        $query = $event->subject()->query;
        $query->contain([
            'Regions'
        ]);

        $this->set('regions', $this->Blocks->Regions->find('list'));
    }

    public function beforeCrudRender()
    {
        $this->set('roles', $this->Roles->find('list'));
    }

    public function implementedEvents()
    {
        return parent::implementedEvents() + [
            'Crud.beforePaginate' => 'beforePaginate',
            'Crud.beforeRender' => 'beforeCrudRender',
        ];
    }
}