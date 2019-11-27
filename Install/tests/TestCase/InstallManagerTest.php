<?php

namespace Croogo\Install\Test;

use Croogo\Core\TestSuite\TestCase;
use Croogo\Install\InstallManager;

class InstallManagerTest extends TestCase
{
    public $datasourceText = <<<HEREDOC
    'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            //'port' => 'nonstandard_port_number',
            'username' => 'my_app',
            'password' => 'secret',
            'database' => 'my_app',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
            //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
        ],
        'test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            //'port' => 'nonstandard_port_number',
            'username' => 'my_app',
            'password' => 'secret',
            'database' => 'test_myapp',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
        ],
    ],

    /**
     * Configures logging options
     */
    'Log' => [
HEREDOC;

    public function setUp()
    {
        $this->InstallManager = new InstallManager();
    }

    public function datasourceTextProvider()
    {
        yield [];
    }

    public function testRegexes()
    {
        $this->assertTrue(true);
    }
}
