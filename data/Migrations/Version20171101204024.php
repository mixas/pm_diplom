<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171101204024 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('time_log');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('task_id', 'integer', ['notnull' => true]);
        $table->addColumn('spent_time', 'integer', ['notnull' => true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'],
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'time_log_user_id_fk');
        $table->addForeignKeyConstraint('task', ['task_id'], ['id'],
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'time_log_task_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
