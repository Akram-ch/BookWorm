<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220721092401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A33193CB796C');
        $this->addSql('DROP INDEX UNIQ_CBE5A33193CB796C ON book');
        $this->addSql('ALTER TABLE book ADD document VARCHAR(255) NOT NULL, DROP file_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book ADD file_id INT NOT NULL, DROP document');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A33193CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBE5A33193CB796C ON book (file_id)');
    }
}
