<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221113161733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carte_banquaire (id INT AUTO_INCREMENT NOT NULL, compte_banquaire_id INT DEFAULT NULL, num_compte VARCHAR(16) NOT NULL, date DATE NOT NULL, cvv2 VARCHAR(3) NOT NULL, INDEX IDX_A166B7D79A5A34B9 (compte_banquaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carte_banquaire ADD CONSTRAINT FK_A166B7D79A5A34B9 FOREIGN KEY (compte_banquaire_id) REFERENCES compte_bancaire (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carte_banquaire DROP FOREIGN KEY FK_A166B7D79A5A34B9');
        $this->addSql('DROP TABLE carte_banquaire');
    }
}
