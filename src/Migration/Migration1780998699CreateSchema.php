<?php declare(strict_types=1);

namespace Frosh\Mjml\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1780998699CreateSchema extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1780998699;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `frosh_mjml_component` (
                `id`             BINARY(16)   NOT NULL,
                `technical_name` VARCHAR(255) NOT NULL,
                `type`           VARCHAR(32)  NOT NULL DEFAULT \'fragment\',
                `created_at`     DATETIME(3)  NOT NULL,
                `updated_at`     DATETIME(3)  NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.frosh_mjml_component.technical_name` (`technical_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `frosh_mjml_component_translation` (
                `frosh_mjml_component_id` BINARY(16)   NOT NULL,
                `language_id`             BINARY(16)   NOT NULL,
                `label`                   VARCHAR(255) NULL,
                `mjml_content`            LONGTEXT     NULL,
                `created_at`              DATETIME(3)  NOT NULL,
                `updated_at`              DATETIME(3)  NULL,
                PRIMARY KEY (`frosh_mjml_component_id`, `language_id`),
                CONSTRAINT `fk.frosh_mjml_component_translation.id` FOREIGN KEY (`frosh_mjml_component_id`)
                    REFERENCES `frosh_mjml_component` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.frosh_mjml_component_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `frosh_mjml_mail_template` (
                `id`               BINARY(16)  NOT NULL,
                `mail_template_id` BINARY(16)  NOT NULL,
                `enabled`          TINYINT(1)  NOT NULL DEFAULT 0,
                `created_at`       DATETIME(3) NOT NULL,
                `updated_at`       DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.frosh_mjml_mail_template.mail_template_id` (`mail_template_id`),
                CONSTRAINT `fk.frosh_mjml_mail_template.mail_template_id` FOREIGN KEY (`mail_template_id`)
                    REFERENCES `mail_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `frosh_mjml_mail_template_translation` (
                `frosh_mjml_mail_template_id` BINARY(16)  NOT NULL,
                `language_id`                 BINARY(16)  NOT NULL,
                `mjml_content`                LONGTEXT    NULL,
                `created_at`                  DATETIME(3) NOT NULL,
                `updated_at`                  DATETIME(3) NULL,
                PRIMARY KEY (`frosh_mjml_mail_template_id`, `language_id`),
                CONSTRAINT `fk.frosh_mjml_mt_translation.id` FOREIGN KEY (`frosh_mjml_mail_template_id`)
                    REFERENCES `frosh_mjml_mail_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.frosh_mjml_mt_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
