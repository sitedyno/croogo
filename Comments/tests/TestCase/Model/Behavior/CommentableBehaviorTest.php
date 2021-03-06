<?php

namespace Croogo\Comments\Test\TestCase\Model\Behavior;

use Croogo\TestSuite\CroogoTestCase;

class CommentableBehaviorTest extends CroogoTestCase
{

    public $setupSettings = false;

    public $fixtures = [
        'plugin.comments.comment',
        'plugin.nodes.node',
        'plugin.users.user',
        'plugin.taxonomy.type',
    ];

    public function setUp()
    {
        $this->Comment = ClassRegistry::init('Comments.Comment');
        $this->Comment->bindModel([
            'belongsTo' => [
                'Node' => [
                    'className' => 'Node',
                    'foreignKey' => 'foreign_key',
                    'conditions' => [
                        'model' => 'Node',
                    ],
                ],
            ],
        ], false);

        $this->Comment->Node->Behaviors->load('Comments.Commentable');
    }

    public function tearDown()
    {
        ClassRegistry::flush();
    }

/**
 * Test Commentable Add
 */
    public function testCommentableAdd()
    {
        $count = $this->Comment->find('count', ['recursive' => -1]);

        $this->Comment->Node->id = 1;
        $result = $this->Comment->Node->addComment([
            'Comment' => [
                'body' => 'hello world',
                'name' => 'Your name',
                'email' => 'your@email.dev',
                'status' => 1,
                'website' => '/',
                'ip' => '127.0.0.1',
            ],
        ]);

        $this->assertTrue($result);
        $result = $this->Comment->find('count', ['recursive' => -1]);
        $this->assertEquals($count + 1, $result);
    }

/**
 * @expectedException UnexpectedValueException
 */
    public function testCommentableAddWithMissingId()
    {
        unset($this->Comment->Node->id);
        $this->Comment->Node->addComment([]);
    }

/**
 * Test Get Type Setting
 */
    public function testGetTypeSetting()
    {
        $result = $this->Comment->Node->getTypeSetting([
            'Node' => [
                'type' => 'blog',
            ],
        ]);
        $expected = [
            'commentable' => true,
            'autoApprove' => true,
            'spamProtection' => false,
            'captchaProtection' => false,
        ];
        $this->assertEquals($expected, $result);
    }
}
