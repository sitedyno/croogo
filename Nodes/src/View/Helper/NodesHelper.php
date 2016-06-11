<?php

namespace Croogo\Nodes\View\Helper;

use Cake\Event\Event;
use Cake\View\Helper;
use Cake\View\View;
use Croogo\Core\Croogo;
use Croogo\Core\Utility\StringConverter;

/**
 * Nodes Helper
 *
 * @category Helper
 * @package  Croogo.Nodes
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class NodesHelper extends Helper
{

/**
 * Other helpers used by this helper
 *
 * @var array
 * @access public
 */
    public $helpers = [
        'Html',
        'Form',
        'Session',
        'Croogo.Layout',
    ];

/**
 * Current Node
 *
 * @var array
 * @access public
 */
    public $node = null;

/**
 * StringConverter instance
 *
 * @var StringConverter
 */
    protected $_converter = null;

/**
 * constructor
 */
    public function __construct(View $view, $settings = [])
    {
        parent::__construct($view);
        $this->_converter = new StringConverter();
        $this->_setupEvents();
    }

/**
 * setup events
 */
    protected function _setupEvents()
    {
        $events = [
            'Helper.Layout.beforeFilter' => [
                'callable' => 'filter', 'passParams' => true,
            ],
        ];
        $eventManager = $this->_View->eventManager();
        foreach ($events as $name => $config) {
            $eventManager->on($name, $config, [$this, 'filter']);
        }
    }

/**
 * Show nodes list
 *
 * @param string $alias Node query alias
 * @param array $options (optional)
 * @return string
 */
    public function nodeList($alias, $options = [])
    {
        $_options = [
            'link' => true,
            'plugin' => 'nodes',
            'controller' => 'nodes',
            'action' => 'view',
            'element' => 'Croogo/Nodes.node_list',
        ];
        $options = array_merge($_options, $options);
        $output = '';
        if (isset($this->_View->viewVars['nodesForLayout'][$alias])) {
            $nodes = $this->_View->viewVars['nodesForLayout'][$alias];
            $output = $this->_View->element($options['element'], [
                'alias' => $alias,
                'nodesList' => $this->_View->viewVars['nodesForLayout'][$alias],
                'options' => $options,
            ]);
        }
        return $output;
    }

/**
 * Filter content for Nodes
 *
 * Replaces [node:unique_name_for_query] or [n:unique_name_for_query] with Nodes list
 *
 * @param Event $event
 * @return string
 */
    public function filter(Event $event, $options = [])
    {
        preg_match_all('/\[(node|n):([A-Za-z0-9_\-]*)(.*?)\]/i', $event->data['content'], $tagMatches);
        for ($i = 0, $ii = count($tagMatches[1]); $i < $ii; $i++) {
            $regex = '/(\S+)=[\'"]?((?:.(?![\'"]?\s+(?:\S+)=|[>\'"]))+.)[\'"]?/i';
            preg_match_all($regex, $tagMatches[3][$i], $attributes);
            $alias = $tagMatches[2][$i];
            $options = [];
            for ($j = 0, $jj = count($attributes[0]); $j < $jj; $j++) {
                $options[$attributes[1][$j]] = $attributes[2][$j];
            }
            $event->data['content'] = str_replace($tagMatches[0][$i], $this->nodeList($alias, $options), $event->data['content']);
        }
        return $event->data;
    }

/**
 * Set current Node
 *
 * @param array $node
 * @return void
 */
    public function set($node)
    {
        $event = Croogo::dispatchEvent('Helper.Nodes.beforeSetNode', $this->_View, [
            'node' => $node,
        ]);
        $this->node = $event->data['node'];
        $this->Layout->hook('afterSetNode');
        Croogo::dispatchEvent('Helper.Nodes.afterSetNode', $this->_View, [
            'node' => $this->node
        ]);
    }

/**
 * Get value of a Node field
 *
 * @param string $field
 * @return string
 */
    public function field($field = 'id', $value = null)
    {
        if ($value) {
            return $this->node->set($field, $value);
        }

        return $this->node->get($field);
    }

/**
 * Node info
 *
 * @param array $options
 * @return string
 */
    public function info($options = [])
    {
        $_options = [
            'element' => 'Croogo/Nodes.node_info',
        ];
        $options = array_merge($_options, $options);

        $output = $this->Layout->hook('beforeNodeInfo');
        $output .= $this->_View->element($options['element']);
        $output .= $this->Layout->hook('afterNodeInfo');
        return $output;
    }

/**
 * Node excerpt (summary)
 *
 * Options:
 * - `element`: Element to use when rendering excerpt
 * - `body`: Extract first paragraph from body as excerpt. Default is `false`
 *
 * @param array $options
 * @return string
 */
    public function excerpt($options = [])
    {
        $_options = [
            'element' => 'Croogo/Nodes.node_excerpt',
            'body' => false,
        ];
        $options = array_merge($_options, $options);

        $excerpt = $this->node->excerpt;
        if ($options['body'] && empty($excerpt)) {
            $excerpt = $this->_converter->firstPara(
                $this->node->body,
                ['tag' => true]
            );
        }

        $output = $this->Layout->hook('beforeNodeExcerpt');
        $output .= $this->_View->element($options['element'], compact('excerpt'));
        $output .= $this->Layout->hook('afterNodeExcerpt');
        return $output;
    }

/**
 * Node body
 *
 * @param array $options
 * @return string
 */
    public function body($options = [])
    {
        $_options = [
            'element' => 'Croogo/Nodes.node_body',
        ];
        $options = array_merge($_options, $options);

        $output = $this->Layout->hook('beforeNodeBody');
        $output .= $this->_View->element($options['element']);
        $output .= $this->Layout->hook('afterNodeBody');
        return $output;
    }

/**
 * Node more info
 *
 * @param array $options
 * @return string
 */
    public function moreInfo($options = [])
    {
        $_options = [
            'element' => 'Croogo/Nodes.node_more_info',
        ];
        $options = array_merge($_options, $options);

        $output = $this->Layout->hook('beforeNodeMoreInfo');
        $output .= $this->_View->element($options['element']);
        $output .= $this->Layout->hook('afterNodeMoreInfo');
        return $output;
    }

/**
 * Convenience method to generate url to a node or current node
 *
 * @param array $node Node data
 * @return string
 */
    public function url($url = null, $full = false)
    {
        if ($url === null && $this->node) {
            $url = $this->node;
        }
        $alias = is_array($url) ? key($url) : null;
        if (isset($url[$alias]['url'])) {
            $url = $url[$alias]['url'];
        } elseif (isset($url[$alias]['type']) && isset($url[$alias]['slug'])) {
            $url = [
                'plugin' => 'nodes',
                'controller' => 'nodes',
                'action' => 'view',
                'type' => $url[$alias]['type'],
                'slug' => $url[$alias]['slug']
            ];
        }
        return parent::url($url, $full);
    }
}