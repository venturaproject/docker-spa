<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240505095005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates service_schedules and service_bookings tables';
    }

    public function up(Schema $schema): void
    {
        // Crear la tabla service_schedules
        $this->addSql('CREATE TABLE service_schedules (
            id VARCHAR(36) NOT NULL,
            service_id VARCHAR(36) NOT NULL,
            day DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            PRIMARY KEY(id),
            CONSTRAINT fk_service FOREIGN KEY (service_id) REFERENCES spa_services (id)
        )');

        // Crear la tabla service_bookings
        $this->addSql('CREATE TABLE service_bookings (
            id VARCHAR(36) NOT NULL,
            service_id VARCHAR(36) NOT NULL,
            client_name VARCHAR(255) NOT NULL,
            client_email VARCHAR(255) NOT NULL,
            service_day DATE NOT NULL,
            service_time TIME NOT NULL,
            PRIMARY KEY(id),
            CONSTRAINT fk_booking_service FOREIGN KEY (service_id) REFERENCES spa_services (id)
        )');
    }

    public function down(Schema $schema): void
    {
        // Eliminar la tabla service_bookings
        $this->addSql('DROP TABLE service_bookings');

        // Eliminar la tabla service_schedules
        $this->addSql('DROP TABLE service_schedules');
    }
}
