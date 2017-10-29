<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171029090608 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Create 'user_project' table implements (relation between project and assigned user for it)
        $table = $schema->createTable('user_project');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('project_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'],
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_project_user_id_fk');
        $table->addForeignKeyConstraint('project', ['project_id'], ['id'],
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_project_project_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('user_project');
    }
}
