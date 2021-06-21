<?php

namespace Croogo\Meta\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * Meta Behavior
 *
 * @category Behavior
 * @package  Croogo.Meta.Model.Behavior
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class MetaBehavior extends Behavior
{

    /**
     * Setup
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config = [])
    {
        $this->_table->hasMany('Meta', [
            'className' => 'Croogo/Meta.Meta',
            'foreignKey' => 'foreign_key',
            'dependent' => true,
            'conditions' => [
                'Meta.model' => $this->_table->getRegistryAlias(),
            ],
            'order' => 'Meta.key ASC',
            'cascadeCallbacks' => true
        ]);

        $this->_table->Meta
            ->belongsTo($this->_table->getAlias(), [
                'targetTable' => $this->_table,
                'foreignKey' => 'foreign_key',
                'conditions' => [
                    'Meta.model' => $this->_table->getRegistryAlias(),
                ],
            ]);
    }

    /**
     * beforeFind callback
     *
     * @return array
     */
    public function beforeFind(Event $event, Query $query)
    {
        $query
            ->contain(['Meta'])
            ->formatResults(function ($resultSet) {
                return $resultSet->map(function ($entity) {
                    if (!$entity instanceof EntityInterface) {
                        return $entity;
                    }
                    $this->_table->dispatchEvent('Model.Meta.formatFields', compact('entity'));
                    $customFields = [];
                    if (!empty($entity->meta)) {
                        $customFields = Hash::combine($entity->meta, '{n}.key', '{n}.value');
                    }
                    $entity->custom_fields = $customFields;

                    return $entity;
                });
            });

        return $query;
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    public function prepareData($data)
    {
        return $this->_prepareMeta($data);
    }

    /**
     * Protected method for MetaBehavior::prepareData()
     *
     * @param ArrayObject $data
     * @param ArrayObject $options
     * @return void
     */
    protected function _prepareMeta(ArrayObject $data, ArrayObject $options)
    {
        if (isset($data['meta']) &&
            is_array($data['meta']) &&
            count($data['meta']) > 0 &&
            !Hash::numeric(array_keys($data['meta']))
        ) {
            $meta = $data['meta'];
            $data['meta'] = [];
            $i = 0;
            foreach ($meta as $metaArray) {
                $data['meta'][$i] = $metaArray;
                $i++;
            }

            if (isset($options['associated']) && !(isset($options['associated']['Meta']) || in_array('Meta', $options['associated']))) {
                $options['associated'][] = 'Meta';
            }
        }

        $this->_table->dispatchEvent('Model.Meta.prepareFields', compact('data', 'options'));
    }

    /**
     * Handle Model.beforeMarshal event
     *
     * @param Event $event Event object
     * @return void
     */
    public function beforeMarshal(Event $event)
    {
        $this->_prepareMeta($event->getData('data'), $event->getData('options'));
    }

    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if (!$entity->has('meta')) {
            return;
        }

        if (isset($options['associated']) &&
            !(isset($options['associated']['meta']) || in_array('meta', $options['associated']))
        ) {
            $options['associated'][] = 'meta';
        }

        foreach ($entity->meta as &$meta) {
            $meta->model = $entity->getSource();
        }
    }
}
