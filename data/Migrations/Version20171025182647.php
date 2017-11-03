<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171025182647 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create 'task_status' table
        $table = $schema->createTable('comment');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('task_id', 'integer', ['notnull'=>true]);
        $table->addColumn('comment_text', 'string', ['notnull'=>true]);
        $table->addColumn('created_date', 'datetime', ['notnull'=>true]);
        $table->addColumn('updated_date', 'datetime', ['notnull'=>false]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'],
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'comment_user_id_fk');
        $table->addForeignKeyConstraint('task', ['task_id'], ['id'],
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'comment_task_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('comment');
    }
}
