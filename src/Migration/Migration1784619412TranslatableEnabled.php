<?php declare(strict_types=1);

namespace Frosh\Mjml\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1784619412TranslatableEnabled extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1784619412;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `frosh_mjml_mail_template_translation`
            ADD COLUMN `enabled` TINYINT(1) NULL AFTER `language_id`
        ');

        $connection->executeStatement('
            INSERT INTO `frosh_mjml_mail_template_translation`
                (`frosh_mjml_mail_template_id`, `language_id`, `enabled`, `created_at`)
            SELECT `id`, :languageId, `enabled`, NOW(3)
            FROM `frosh_mjml_mail_template`
            WHERE `enabled` = 1
            ON DUPLICATE KEY UPDATE `enabled` = VALUES(`enabled`)
        ', ['languageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM)]);
    }

    public function updateDestructive(Connection $connection): void
    {
        $columns = $connection->fetchAllAssociative('SHOW COLUMNS FROM `frosh_mjml_mail_template` LIKE \'enabled\'');
        if ($columns === []) {
            return;
        }

        $connection->executeStatement('ALTER TABLE `frosh_mjml_mail_template` DROP COLUMN `enabled`');
    }
}
