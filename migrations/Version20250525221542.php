<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525221542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP CONSTRAINT fk_d22944589d86650f
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d22944589d86650f
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP user_id_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D2294458A76ED395 ON feedback (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP CONSTRAINT FK_D2294458A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D2294458A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD user_id_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT fk_d22944589d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_d22944589d86650f ON feedback (user_id_id)
        SQL);
    }
}
