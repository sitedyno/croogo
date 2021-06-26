<?php
use Migrations\AbstractMigration;

class TaxonomySqliteTimestampfields extends AbstractMigration
{
    public function up()
    {
        $driver = $this->getAdapter()
           ->getCakeConnection()
           ->getDriver();

        if ($driver instanceof Sqlite) {
        }
    }

    public function down()
    {
    }
}
