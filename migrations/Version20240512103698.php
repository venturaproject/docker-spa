<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240512103698 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Description of your migration here';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('
            CREATE TABLE spa_services (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                price INT NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY(id)
            )
        ');
        
        $this->addSql('
            CREATE TABLE service_schedules (
                id UUID NOT NULL,
                service_id UUID NOT NULL,
                day DATE NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                PRIMARY KEY(id),
                FOREIGN KEY (service_id) REFERENCES spa_services(id)
            )
        ');

        $this->addSql('
            CREATE TABLE service_bookings (
                id UUID NOT NULL,
                service_id UUID NOT NULL,
                client_name VARCHAR(255) NOT NULL,
                client_email VARCHAR(255) NOT NULL,
                service_day DATE NOT NULL,
                service_time TIME NOT NULL,
                PRIMARY KEY(id),
                FOREIGN KEY (service_id) REFERENCES spa_services(id)
            )
        ');

        // Insertar datos en spa_services
        $this->addSql('
            INSERT INTO spa_services (id, name, price, created_at) 
            VALUES 
                (UUID(), \'Tratamiento Facial\', 75, \'2024-05-05 11:46:25\'),
                (UUID(), \'Masaje Relajante\', 55, \'2024-05-05 11:45:36\')
        ');

        // Insertar datos en service_schedules
        $this->addSql('
            INSERT INTO service_schedules (id, service_id, day, start_time, end_time) 
            VALUES 
                (UUID(), \'2b5d7cf2-6591-4631-b88e-33926dfeee30\', \'2024-05-05\', \'11:36:16\', \'12:00:35\'),
                (UUID(), \'2b5d7cf2-6591-4631-b88e-33926dfeee30\', \'2024-05-05\', \'14:02:31\', \'15:02:39\'),
                (UUID(), \'c960a3f2-0737-4db2-9e23-fa59240a91a0\', \'2024-05-06\', \'09:03:50\', \'10:04:00\'),
                (UUID(), \'c960a3f2-0737-4db2-9e23-fa59240a91a0\', \'2024-05-06\', \'14:05:00\', \'15:05:07\'),
                (UUID(), \'c960a3f2-0737-4db2-9e23-fa59240a91a0\', \'2024-05-07\', \'14:06:13\', \'15:06:19\')
        ');

        // Insertar datos en service_bookings
        $this->addSql('
            INSERT INTO service_bookings (id, service_id, client_name, client_email, service_day, service_time) 
            VALUES 
                (UUID(), \'c960a3f2-0737-4db2-9e23-fa59240a91a0\', \'John Doe\', \'john.doe@example.com\', \'2024-12-31\', \'15:00:00\'),
                (UUID(), \'c960a3f2-0737-4db2-9e23-fa59240a91a0\', \'John Doe\', \'john.doe@example.com\', \'2025-05-08\', \'15:00:00\'),
                (UUID(), \'c960a3f2-0737-4db2-9e23-fa59240a91a0\', \'John Doe\', \'john.doe@example.com\', \'2025-05-13\', \'15:00:00\'),
                (UUID(), \'2b5d7cf2-6591-4631-b88e-33926dfeee30\', \'Dennis Doe\', \'ddoe@example.com\', \'2024-05-06\', \'09:12:14\'),
                (UUID(), \'2b5d7cf2-6591-4631-b88e-33926dfeee30\', \'Jane Doe\', \'janedoe@example.com\', \'2024-05-05\', \'14:04:38\')
        ');
    }

    public function down(Schema $schema) : void
    {
        // Esto eliminará las tablas si decides hacer un rollback de la migración
        $this->addSql('DROP TABLE spa_services');
        $this->addSql('DROP TABLE service_schedules');
        $this->addSql('DROP TABLE service_bookings');
    }
}
