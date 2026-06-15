<?php declare(strict_types=1);

namespace Frosh\Mjml\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1781523824InsertDefaultComponents extends MigrationStep
{
    /**
     * @var array<string, array<string, string>>
     */
    private const LINE_ITEMS_HEADERS = [
        'de-DE' => [
            '###PRODUCT###' => 'Produkt',
            '###DESCRIPTION###' => 'Bezeichnung',
            '###QUANTITY###' => 'Menge',
            '###PRICE###' => 'Preis',
            '###TOTAL###' => 'Summe',
        ],
        'en-GB' => [
            '###PRODUCT###' => 'Product',
            '###DESCRIPTION###' => 'Description',
            '###QUANTITY###' => 'Qty',
            '###PRICE###' => 'Price',
            '###TOTAL###' => 'Total',
        ],
    ];

    private const DEFAULT_LAYOUT = <<<'MJML'
        <mjml>
            <mj-head>
                <mj-attributes>
                    <mj-all font-family="Helvetica, Arial, sans-serif" />
                    <mj-text font-size="16px" color="#333333" line-height="1.6" />
                </mj-attributes>
            </mj-head>
            <mj-body background-color="#f4f4f5">
                <mj-section background-color="#ffffff" padding="24px 0">
                    <mj-column>
                        <mj-text align="center" font-size="22px" font-weight="bold">
                            {{ salesChannel.translated.name|default('Sales Channel Name') }}
                        </mj-text>
                    </mj-column>
                </mj-section>

                <mj-section background-color="#ffffff">
                    <mj-column>
                        {% block content %}{% endblock %}
                    </mj-column>
                </mj-section>

                <mj-section>
                    <mj-column>
                        <mj-text align="center" font-size="14px" color="#888888">
                            © {{ "now"|date("Y") }} {{ salesChannel.translated.name|default('Sales Channel Name') }}
                        </mj-text>
                    </mj-column>
                </mj-section>
            </mj-body>
        </mjml>
        MJML;

    private const BUTTON_FRAGMENT = <<<'MJML'
        <mj-button background-color="#0d6efd" color="#ffffff" border-radius="4px" href="{{ url|default('#') }}">
            {{ text|default('Learn more') }}
        </mj-button>
        MJML;

    private const LINE_ITEMS_FRAGMENT = <<<'MJML'
        {% set currencyIsoCode = order.currency.isoCode %}
        <mj-table cellpadding="6" font-size="14px" color="#333333" line-height="1.4">
            <tr style="text-align:left; border-bottom:2px solid #cccccc;">
                <th style="padding:6px;">###PRODUCT###</th>
                <th style="padding:6px;">###DESCRIPTION###</th>
                <th style="padding:6px; text-align:center;">###QUANTITY###</th>
                <th style="padding:6px; text-align:right;">###PRICE###</th>
                <th style="padding:6px; text-align:right;">###TOTAL###</th>
            </tr>
            {% for lineItem in order.nestedLineItems %}
                {% set nestingLevel = 0 %}
                {% set nestedItem = lineItem %}
                {% block lineItem %}
                    <tr style="border-bottom:1px solid #eeeeee;">
                        <td style="padding:6px; vertical-align:top;">{% if nestedItem.cover is defined and nestedItem.cover is not null %}<img src="{{ nestedItem.cover.url }}" alt="" width="75" style="display:block; max-width:75px; height:auto;" />{% endif %}</td>
                        <td style="padding:6px; vertical-align:top;">
                            <div{% if nestingLevel > 0 %} style="padding-left: {{ (nestingLevel + 1) * 10 }}px;"{% endif %}>{{ nestedItem.label }}</div>
                            {% if nestedItem.payload.productNumber is defined %}<div style="color:#888888; font-size:12px;">{{ nestedItem.payload.productNumber }}</div>{% endif %}
                            {% if nestedItem.payload.options is defined and nestedItem.payload.options|length >= 1 %}
                                <div style="color:#888888; font-size:12px;">{% for option in nestedItem.payload.options %}{{ option.group }}: {{ option.option }}{% if nestedItem.payload.options|last != option %} | {% endif %}{% endfor %}</div>
                            {% endif %}
                        </td>
                        <td style="padding:6px; text-align:center; vertical-align:top;">{{ nestedItem.quantity }}</td>
                        <td style="padding:6px; text-align:right; vertical-align:top;">{{ nestedItem.unitPrice|currency(currencyIsoCode) }}</td>
                        <td style="padding:6px; text-align:right; vertical-align:top;">{{ nestedItem.totalPrice|currency(currencyIsoCode) }}</td>
                    </tr>
                    {% if nestedItem.children.count > 0 %}
                        {% set nestingLevel = nestingLevel + 1 %}
                        {% for lineItem in nestedItem.children %}
                            {% set nestedItem = lineItem %}
                            {{ block('lineItem') }}
                        {% endfor %}
                    {% endif %}
                {% endblock %}
            {% endfor %}
        </mj-table>
        MJML;

    public function getCreationTimestamp(): int
    {
        return 1781523824;
    }

    public function update(Connection $connection): void
    {
        $languageByLocale = $this->resolveLanguageIds($connection);

        foreach ($this->defaultComponents() as $component) {
            if ($this->exists($connection, $component['technicalName'])) {
                continue;
            }

            $this->insertComponent($connection, $component, $languageByLocale);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function exists(Connection $connection, string $technicalName): bool
    {
        return (bool) $connection->fetchOne(
            'SELECT 1 FROM `frosh_mjml_component` WHERE `technical_name` = :name LIMIT 1',
            ['name' => $technicalName],
        );
    }

    /**
     * @param array{technicalName: string, type: string, translations: array<string, array{label: string, content: string}>} $component
     * @param array<string, string> $languageByLocale
     */
    private function insertComponent(Connection $connection, array $component, array $languageByLocale): void
    {
        $id = Uuid::randomBytes();
        $now = (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $connection->insert('frosh_mjml_component', [
            'id' => $id,
            'technical_name' => $component['technicalName'],
            'type' => $component['type'],
            'created_at' => $now,
        ]);

        $translations = $component['translations'];

        // The system language is always present and serves as the fallback translation.
        $fallback = $translations['en-GB'];
        $writtenLanguages = [];
        $this->insertTranslation($connection, $id, Defaults::LANGUAGE_SYSTEM, $fallback['label'], $fallback['content'], $now);
        $writtenLanguages[strtolower(Defaults::LANGUAGE_SYSTEM)] = true;

        foreach ($translations as $locale => $translation) {
            $languageId = $languageByLocale[$locale] ?? null;
            if ($languageId === null || isset($writtenLanguages[$languageId])) {
                continue;
            }

            $this->insertTranslation($connection, $id, $languageId, $translation['label'], $translation['content'], $now);
            $writtenLanguages[$languageId] = true;
        }
    }

    private function insertTranslation(Connection $connection, string $componentId, string $languageIdHex, string $label, string $content, string $now): void
    {
        $connection->insert('frosh_mjml_component_translation', [
            'frosh_mjml_component_id' => $componentId,
            'language_id' => Uuid::fromHexToBytes($languageIdHex),
            'label' => $label,
            'mjml_content' => $content,
            'created_at' => $now,
        ]);
    }

    /**
     * @return array<string, string> map of locale code to language id (hex)
     */
    private function resolveLanguageIds(Connection $connection): array
    {
        /** @var array<string, string> $languages */
        $languages = $connection->fetchAllKeyValue(
            'SELECT `locale`.`code`, LOWER(HEX(`language`.`id`))
             FROM `language`
             INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`',
        );

        return $languages;
    }

    /**
     * @return list<array{technicalName: string, type: string, translations: array<string, array{label: string, content: string}>}>
     */
    private function defaultComponents(): array
    {
        return [
            [
                'technicalName' => 'default',
                'type' => 'layout',
                'translations' => [
                    'de-DE' => ['label' => 'Standard-Layout', 'content' => self::DEFAULT_LAYOUT],
                    'en-GB' => ['label' => 'Default layout', 'content' => self::DEFAULT_LAYOUT],
                ],
            ],
            [
                'technicalName' => 'button',
                'type' => 'fragment',
                'translations' => [
                    'de-DE' => ['label' => 'Button (Call-to-Action)', 'content' => self::BUTTON_FRAGMENT],
                    'en-GB' => ['label' => 'Button (call to action)', 'content' => self::BUTTON_FRAGMENT],
                ],
            ],
            [
                'technicalName' => 'line-items',
                'type' => 'fragment',
                'translations' => [
                    'de-DE' => ['label' => 'Bestellpositionen', 'content' => $this->lineItemsContent('de-DE')],
                    'en-GB' => ['label' => 'Order line items', 'content' => $this->lineItemsContent('en-GB')],
                ],
            ],
        ];
    }

    private function lineItemsContent(string $locale): string
    {
        $headers = self::LINE_ITEMS_HEADERS[$locale];

        return str_replace(array_keys($headers), array_values($headers), self::LINE_ITEMS_FRAGMENT);
    }
}
