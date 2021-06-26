<?php

namespace Croogo\FileManager\Event;

use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Char0n\FFMpegPHP\Movie;
use Croogo\Core\Croogo;
use Croogo\Core\Nav;

/**
 * FileManagerEventHandler
 *
 * @category Event
 * @package  Croogo.FileManager.Event
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class FileManagerEventHandler implements EventListenerInterface
{

    /**
     * implementedEvents
     */
    public function implementedEvents()
    {
        return [
            'Controller.FileManager/Attachment.newAttachment' => [
                'callable' => 'onNewAttachment',
            ],
            'Croogo.setupAdminData' => [
                'callable' => 'onSetupAdminData',
            ],
            'Controller.Links.setupLinkChooser' => [
                'callable' => 'onSetupLinkChooser',
            ],
        ];
    }

    /**
     * Registers usage when new attachment is created and attached to a resource
     */
    public function onNewAttachment($event)
    {
        $controller = $event->getSubject();
        $request = $controller->request;
        $attachment = $event->getData('attachment');

        // create poster for video, ideally should be done via a job queue
        $Attachments = TableRegistry::get('Croogo/FileManager.Attachments');
        if (
            strstr($attachment->asset->mime_type, 'video') !== false &&
            class_exists('Char0n\FFMpegPHP\Movie')
        ) {
            $Attachments->createVideoThumbnail($attachment->id);
        }

        if (empty($attachment->asset->asset_usage)) {
            Log::error('No asset usage record to register');

            return;
        }

        $usage = $attachment->asset->asset_usage[0];
        $Usage = TableRegistry::get('Croogo/FileManager.AssetUsages');
        $data = $Usage->newEntity([
            'asset_id' => $attachment->asset->id,
            'model' => $usage['model'],
            'foreign_key' => $usage['foreign_key'],
            'featured_image' => $usage['featured_image'],
        ]);
        $result = $Usage->save($data);
        if (!$result) {
            Log::error('Asset Usage registration failed');
            Log::error(print_r($Usage->validationErrors, true));
        }
        $event->result = $result;
    }

    /**
     * Setup Link chooser values
     *
     * @return void
     */
    public function onSetupLinkChooser($event)
    {
        $linkChoosers = [];
        $linkChoosers['Images'] = [
            'title' => 'Images',
            'description' => 'Attachments with an image mime type.',
            'url' => [
                'plugin' => 'Croogo/FileManager',
                'controller' => 'Attachments',
                'action' => 'index',
                '?' => [
                    'chooser_type' => 'image',
                    'chooser' => 1,
                    'KeepThis' => true,
                    'TB_iframe' => true,
                    'height' => 400,
                    'width' => 600
                ]
            ]
        ];
        $linkChoosers['Files'] = [
            'title' => 'Files',
            'description' => 'Attachments with other mime types, ie. pdf, xls, doc, etc.',
            'url' => [
                'plugin' => 'Croogo/FileManager',
                'controller' => 'Attachments',
                'action' => 'index',
                '?' => [
                    'chooser_type' => 'file',
                    'chooser' => 1,
                    'KeepThis' => true,
                    'TB_iframe' => true,
                    'height' => 400,
                    'width' => 600
                ]
            ]
        ];
        Croogo::mergeConfig('Croogo.linkChoosers', $linkChoosers);
    }

    /**
     * Setup admin data
     */
    public function onSetupAdminData($event)
    {
        Nav::add('media.children.attachments', [
            'title' => __d('croogo', 'Attachments'),
            'url' => [
                'prefix' => 'admin',
                'plugin' => 'Croogo/FileManager',
                'controller' => 'Attachments',
                'action' => 'index',
            ],
        ]);
    }
}
