<?php
namespace Croogo\Install\Test\TestCase\Model;

use Cake\ORM\TableRegistry;
use Croogo\Core\Plugin;
use Croogo\Core\TestSuite\TestCase;
use ReflectionMethod;

class InstallTest extends TestCase
{

    public $fixtures = [
        'plugin.croogo/users.aro',
        'plugin.croogo/install.install_user',
        'plugin.croogo/install.install_role',
    ];

    public function setUp()
    {
        parent::setUp();

        Plugin::load('Croogo/Install');
        $this->Install = TableRegistry::get('Croogo/Install.Install');
    }

    public function testRunMigrationsOk()
    {
        $croogoPlugin = $this->getMockBuilder('InstallManager')
            ->setMethods(['migrate'])
            ->getMock();
        $croogoPlugin->expects($this->any())
                ->method('migrate')
                ->will($this->returnValue(true));
        $this->_runProtectedMethod('_setCroogoPlugin', [$croogoPlugin]);
        $this->assertEquals(true, $this->Install->runMigrations('Users'));
    }

    public function testRunMigrationsFailed()
    {
        $croogoPlugin = $this->getMockBuilder('Plugin')
            ->setMethods(['migrate'])
            ->getMock();
        $croogoPlugin->expects($this->any())
                ->method('migrate')
                ->will($this->returnValue(false));
        $this->_runProtectedMethod('_setCroogoPlugin', [$croogoPlugin]);
        $this->assertEquals(false, $this->Install->runMigrations('Users'));
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
        $count = TableRegistry::get('Users.Users')->find('all');
        $count = $count->count();
        $this->assertEquals($count, 0);
    }

    protected function _runProtectedMethod($name, $args = [])
    {
        $this->skipIf(version_compare(PHP_VERSION, '5.3.0', '<'), 'PHP >= 5.3.0 required to run this test.');
        $method = new ReflectionMethod(get_class($this->Install), $name);
        $method->setAccessible(true);
        return $method->invokeArgs($this->Install, $args);
    }
}
