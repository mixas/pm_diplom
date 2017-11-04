<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171104111850 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('technical_assignment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('project_id', 'integer', ['notnull' => true]);
        $table->addColumn('description', 'string', ['notnull' => true]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->addColumn('date_updated', 'datetime', ['notnull' => false]);
        $table->addColumn('deadline_date', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('project', ['project_id'], ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'], 'technical_assignment_user_id_fk');
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
