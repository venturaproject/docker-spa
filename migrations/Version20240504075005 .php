<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2024XXXXXX extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea la tabla spa_services.';
    }

    public function up(Schema $schema): void
    {
        // SQL para crear la tabla spa_services
        $this->addSql('CREATE TABLE spa_services (
            id VARCHAR(36) NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            price DOUBLE PRECISION NOT NULL, 
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        )');

        // SQL para añadir índices, si es necesario
        $this->addSql('CREATE UNIQUE INDEX idx_name ON spa_services (name);');
    }

    public function down(Schema $schema): void
    {
        // Eliminar la tabla spa_services
        $this->addSql('DROP TABLE spa_services');
    }
}
