<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171126141849 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('attachment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('assigned_task_id', 'integer', ['notnull' => false]);
        $table->addColumn('assigned_technical_assignment_id', 'integer', ['notnull' => false]);
        $table->addColumn('attachment_type', 'integer', ['notnull' => true]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->addColumn('file_link', 'string', ['notnull' => true]);
        $table->addColumn('file_name', 'string', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
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
