<?php

namespace Croogo\Install\Test;

use Croogo\Core\TestSuite\TestCase;
use Croogo\Install\InstallManager;

class InstallManagerTest extends TestCase
{
    public function setUp()
    {
        $this->InstallManager = new InstallManager();
    }

    public function datasourceTextProvider()
    {
        return [
            [
                "
    'Datasources' => [
        'default' => [
            'host' => 'localhost',
        'test' => [
            'driver' => 'Cake\Database\Driver\Mysql',
                ",
                'host',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'username' => 'my_app',
        'test' => [
                ",
                'username',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'password' => 'secret',
        'test' => [
                ",
                'password',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'database' => 'my_app',
        'test' => [
                ",
                'database',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
        'test' => [
                ",
                'driver',
                true
            ],
        ];
    }

    /**
     * @dataProvider datasourceTextProvider
     */
    public function testDatasourceRegex($config, $field, $expected)
    {
        $pattern = str_replace(
            '__FIELD__',
            $field,
            $this->InstallManager::DATASOURCE_REGEX
        );
        $actual = preg_match($pattern, $config, $matches);
        $this->assertEquals($expected, $actual);
    }

    public function DatasourceTestTextProvider()
    {
        return [
            [
                "
    'Datasources' => [
        'default' => [
            'host' => 'localhost',
        'test' => [
            'host' => 'localhost',
    'Log' => [
                ",
                'host',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'username' => 'my_app',
        'test' => [
            'username' => 'my_app',
    'Log' => [
                ",
                'username',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'password' => 'secret',
        'test' => [
            'password' => 'secret',
    'Log' => [
                ",
                'password',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'database' => 'my_app',
        'test' => [
            'database' => 'my_app',
    'Log' => [
                ",
                'database',
                true
            ],
            [
                "
    'Datasources' => [
        'default' => [
            'driver' => 'Cake\Database\Driver\Mysql',
        'test' => [
            'driver' => 'Cake\Database\Driver\Mysql',
    'Log' => [
                ",
                'driver',
                true
            ],
        ];
    }

    /**
     * @dataProvider DatasourceTestTextProvider
     */
    public function testTestDatasourceRegex($config, $field, $expected)
    {
        $pattern = str_replace(
            '__FIELD__',
            $field,
            $this->InstallManager::TEST_DATASOURCE_REGEX
        );
        $actual = preg_match($pattern, $config, $matches);
        $this->assertEquals($expected, $actual);
    }

    public function configProvider()
    {
        return [
            [
                [
                    'host' => 'testhost'
                ],
                "        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'testhost',"
            ],
            [
                [
                    'test-host' => 'testhost'
                ],
                "        'test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'testhost',"
            ]
        ];
    }
    /**
     * @dataProvider configProvider
     */
    public function testCreateDatabaseFile($config, $expected)
    {
        $configPath = CONFIG . 'app.php';
        $originalConfig = file_get_contents($configPath);

        $this->InstallManager->createDatabaseFile($config);
        $newConfig = file_get_contents($configPath);
        file_put_contents($configPath, $originalConfig);
        $this->assertEquals(1, substr_count($newConfig, $expected));
    }
}
