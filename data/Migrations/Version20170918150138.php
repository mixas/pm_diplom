<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170918150138 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Create 'project' table
        $table = $schema->createTable('task');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('project_id', 'integer', ['notnull'=>false]);
        $table->addColumn('assigned_user_id', 'integer', ['notnull'=>false]);
        $table->addColumn('estimate', 'integer', ['notnull'=>false]);
        $table->addColumn('task_title', 'string', ['notnull'=>true, 'length'=>255]);
        $table->addColumn('description', 'string', ['notnull'=>true, 'length'=>5000]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('task');
    }
}
