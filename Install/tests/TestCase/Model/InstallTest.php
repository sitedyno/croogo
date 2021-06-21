<?php
namespace Croogo\Install\Test\TestCase\Model;

use Cake\ORM\TableRegistry;
use Croogo\Core\Plugin;
use Croogo\Core\TestSuite\TestCase;
use ReflectionMethod;

class InstallTest extends TestCase
{

    public $fixtures = [
        'plugin.Croogo/Users.Aro',
        'plugin.Croogo/Install.InstallUser',
        'plugin.Croogo/Install.InstallRole',
    ];

    public function setUp()
    {
        parent::setUp();

        Plugin::load('Croogo/Install');
        $this->Install = TableRegistry::get('Croogo/Install.Install');
    }

    public function testAddAdminUserOk()
    {
        $user = [
            'username' => 'admin',
            'password' => '123456',
        ];
        $this->Install->addAdminUser($user);
        $User = TableRegistry::get('Croogo/Users.Users');

        $count = $User->find('all')->count();
        $this->assertEquals($count, 1);

        $saved = $User->findByUsername('admin');
        $expected = password_hash($user['password'], PASSWORD_BCRYPT);
        $this->assertTrue(
            password_verify($user['password'], $saved->toArray()[0]['password'])
        );
    }

    public function testAddAdminUserBadPassword()
    {
        $this->markTestSkipped('Password validation is disabled in InstallTable.php');
        $user = [
            'username' => 'badadmin',
            'password' => '1234',
        ];
        $this->Install->addAdminUser($user);
        $count = ClassRegistry::init('Users.User')->find('count');
        $this->assertEqual($count, 0);
    }

    protected function _runProtectedMethod($name, $args = [])
    {
        $this->skipIf(version_compare(PHP_VERSION, '5.3.0', '<'), 'PHP >= 5.3.0 required to run this test.');
        $method = new ReflectionMethod(get_class($this->Install), $name);
        $method->setAccessible(true);

        return $method->invokeArgs($this->Install, $args);
    }
}
