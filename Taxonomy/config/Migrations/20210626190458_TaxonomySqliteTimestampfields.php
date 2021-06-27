<?php
use Cake\Database\Driver\Sqlite;
use Migrations\AbstractMigration;

class TaxonomySqliteTimestampfields extends AbstractMigration
{
    public function up()
    {
        $driver = $this->getAdapter()
           ->getCakeConnection()
           ->getDriver();

        if ($driver instanceof Sqlite) {
            $this->table('taxonomies_tmp')
                ->addColumn('parent_id', 'integer', [
                    'default' => null,
                    'limit' => 20,
                    'null' => true,
                ])
                ->addColumn('term_id', 'integer', [
                    'default' => null,
                    'limit' => 10,
                    'null' => false,
                ])
                ->addColumn('vocabulary_id', 'integer', [
                    'default' => null,
                    'limit' => 10,
                    'null' => false,
                ])
                ->addColumn('lft', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true,
                ])
                ->addColumn('rght', 'integer', [
                    'default' => null,
                    'limit' => 11,
                    'null' => true,
                ])
                ->addColumn('created', 'timestamp', [
                    'null' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('modified', 'timestamp', [
                    'null' => true,
                ])
                ->addForeignKey('term_id', 'terms', ['id'], [
                    'constraint' => 'fk_taxonomies2terms',
                    'delete' => 'RESTRICT',
                ])
                ->addForeignKey('vocabulary_id', 'vocabularies', ['id'], [
                    'constraint' => 'fk_taxonomies2vocabularies',
                    'delete' => 'RESTRICT',
                ])
                ->create();
            $taxonomies = $this->fetchAll('select * from taxonomies;');
            foreach ($taxonomies as $taxonomy) {
                $this->table('taxonomies_tmp')
                    ->insert([
                        'id' => $taxonomy['id'],
                        'parent_id' => $taxonomy['parent_id'],
                        'term_id' => $taxonomy['term_id'],
                        'vocabulary_id' => $taxonomy['vocabulary_id'],
                        'lft' => $taxonomy['lft'],
                        'rght' => $taxonomy['rght'],
                    ])
                    ->saveData();
            }
            $this->table('taxonomies')->drop()->save();
            $this->table('taxonomies_tmp')->rename('taxonomies')->update();

            $this->table('types_vocabularies_tmp')
                ->addColumn('type_id', 'integer', [
                    'default' => null,
                    'limit' => 20,
                    'null' => false,
                ])
                ->addColumn('vocabulary_id', 'integer', [
                    'default' => null,
                    'limit' => 20,
                    'null' => false,
                ])
                ->addColumn('weight', 'integer', [
                    'default' => null,
                    'limit' => null,
                    'null' => true,
                ])
                ->addColumn('created', 'timestamp', [
                    'null' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('modified', 'timestamp', [
                    'null' => true,
                ])
                ->create();
            $typesVocabularies = $this->fetchAll('select * from types_vocabularies;');
            foreach ($typesVocabularies as $typeVocabulary) {
                $this->table('types_vocabularies_tmp')
                    ->insert([
                        'id' => $typeVocabulary['id'],
                        'type_id' => $typeVocabulary['type_id'],
                        'vocabulary_id' => $typeVocabulary['vocabulary_id'],
                        'weight' => $typeVocabulary['weight'],
                    ])
                    ->saveData();
            }
            $this->table('types_vocabularies')->drop()->save();
            $this->table('types_vocabularies_tmp')->rename('types_vocabularies')->update();
            $this->table('types_vocabularies')
                ->addIndex(
                    [
                        'type_id', 'vocabulary_id',
                    ],
                    ['unique' => true]
                )
                ->update();

            $this->table('model_taxonomies_tmp')
                ->addColumn('model', 'string', [
                    'default' => null,
                    'limit' => 50,
                    'null' => false,
                ])
                ->addColumn('foreign_key', 'integer', [
                    'default' => null,
                    'limit' => 20,
                    'null' => false,
                ])
                ->addColumn('taxonomy_id', 'integer', [
                    'default' => null,
                    'limit' => 20,
                    'null' => false,
                ])
                ->addColumn('created', 'timestamp', [
                    'null' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('modified', 'timestamp', [
                    'null' => true,
                ])
                ->addForeignKey('taxonomy_id', 'taxonomies', ['id'], [
                    'constraint' => 'fk_model_taxonomies2taxonomies',
                    'update' => 'CASCADE',
                    'delete' => 'CASCADE',
                ])
                ->create();
            $modelTaxonomies = $this->fetchAll('select * from model_taxonomies;');
            foreach ($modelTaxonomies as $modelTaxonomy) {
                $this->table('model_taxonomies_tmp')
                    ->insert([
                        'id' => $modelTaxonomy['id'],
                        'model' => $modelTaxonomy['model'],
                        'foreign_key' => $modelTaxonomy['foreign_key'],
                        'taxonomy_id' => $modelTaxonomy['taxonomy_id'],
                    ])
                    ->saveData();
            }
            $this->table('model_taxonomies')->drop()->save();
            $this->table('model_taxonomies_tmp')->rename('model_taxonomies')->update();
            $this->table('model_taxonomies')
                ->addIndex(
                    [
                        'model', 'foreign_key', 'taxonomy_id',
                    ],
                    ['unique' => true]
                )
                ->update();
        }
    }

    public function down()
    {
        $this->table('taxonomies_tmp')->drop()->save();
        $this->table('types_vocabularies_tmp')->drop()->save();
        $this->table('model_taxonomies_tmp')->drop()->save();
    }
}
