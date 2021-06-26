<?php

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Croogo\Core\Croogo;

Croogo::mergeConfig('Meta.keys', [
    // Uncomment if you need keywords.
    /*
    'meta_keywords' => [
        'label' => __d('seolite', 'Keywords'),
    ],
    */
    'meta_description' => [
        'label' => __d('croogo', 'Description'),
        'help' => __d('croogo', 'When empty, excerpt or first paragraph of body will be used'),
    ],
    'rel_canonical' => [
        'label' => __d('seolite', 'Canonical Page'),
        'type' => 'text',
        'help' => __d('croogo', 'When empty, value from Permalink will be used'),
    ],
]);

$title = 'SEO';
$element = 'Croogo/Meta.admin/seo_tab';
Croogo::hookAdminTab('Admin/Nodes/add', $title, $element);
Croogo::hookAdminTab('Admin/Nodes/edit', $title, $element);

Croogo::hookComponent('*', ['Croogo/Meta.Meta' => ['priority' => 8]]);

Croogo::hookHelper('*', 'Croogo/Meta.Meta');

Inflector::rules('uninflected', ['meta']);
