<?php

namespace presseddigital\linkit\migrations;

use Craft;
use craft\commerce\Plugin as CommercePlugin;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\services\Fields;
use craft\services\Plugins;
use craft\services\ProjectConfig;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\fields\LinkitField;
use presseddigital\linkit\models\Asset as AssetLink;
use presseddigital\linkit\models\Category as CategoryLink;
use presseddigital\linkit\models\Email as EmailLink;
use presseddigital\linkit\models\Entry as EntryLink;
use presseddigital\linkit\models\Phone as PhoneLink;
use presseddigital\linkit\models\Product as ProductLink;
use presseddigital\linkit\models\Url as UrlLink;
use presseddigital\linkit\models\User as UserLink;

class Install extends Migration
{
    public function safeUp(): bool
    {
        $this->upgradeFromCraft2();
        return true;
    }

    protected function upgradeFromCraft2(): void
    {
        // Get Project Config
        $projectConfig = Craft::$app->getProjectConfig();

        // Don't make the same config changes twice
        $schemaVersion = $projectConfig->get('plugins.linkit.schemaVersion', true);
        if ($schemaVersion && version_compare($schemaVersion, '1.0.8', '>=')) {
            return;
        }

        $projectConfig->muteEvents = true;

        // Locate and remove old linkit
        $plugins = $projectConfig->get(ProjectConfig::PATH_PLUGINS) ?? [];
        foreach ($plugins as $pluginHandle => $pluginData) {
            switch ($pluginHandle) {
                case 'fruitlinkit':
                case 'fruit-link-it':
                case 'fruit-linkit':
                    $projectConfig->remove(ProjectConfig::PATH_PLUGINS . '.' . $pluginHandle);
                    break;
            }
        }
        $this->delete('{{%plugins}}', ['handle' => ['fruitlinkit', 'fruit-linkit', 'fruit-link-it']]);

        // Get the field data from the project config
        $fieldConfigs = $projectConfig->get(ProjectConfig::PATH_FIELDS) ?? [];
        $fieldConfigsToMigrate = [];
        foreach ($fieldConfigs as $fieldUid => $fieldConfig) {
            if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'FruitLinkIt') {
                $fieldConfigsToMigrate[$fieldUid] = [
                    'configPath' => ProjectConfig::PATH_FIELDS . '.' . $fieldUid,
                    'config' => $fieldConfig,
                ];
            }
        }

        // Migrate Matrix
        $matrixBlockTypeConfigs = $projectConfig->get('matrixBlockTypes') ?? [];
        foreach ($matrixBlockTypeConfigs as $matrixBlockTypeUid => $matrixBlockTypeConfig) {
            $fieldConfigs = $matrixBlockTypeConfig['fields'] ?? [];
            foreach ($fieldConfigs as $fieldUid => $fieldConfig) {
                if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'FruitLinkIt') {
                    $fieldConfigsToMigrate[$fieldUid] = [
                        'configPath' => 'matrixBlockTypes.' . $matrixBlockTypeUid . '.fields.' . $fieldUid,
                        'config' => $fieldConfig,
                    ];
                }
            }
        }

        // Migrate SuperTable
        $superTableBlockTypeConfigs = $projectConfig->get('superTableBlockTypes') ?? [];
        if ($superTableBlockTypeConfigs) {
            foreach ($superTableBlockTypeConfigs as $superTableBlockTypeUid => $superTableBlockTypeConfig) {
                $fieldConfigs = $superTableBlockTypeConfig['fields'] ?? [];
                foreach ($fieldConfigs as $fieldUid => $fieldConfig) {
                    if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'FruitLinkIt') {
                        $fieldConfigsToMigrate[$fieldUid] = [
                            'configPath' => 'superTableBlockTypes.' . $superTableBlockTypeUid . '.fields.' . $fieldUid,
                            'config' => $fieldConfig,
                        ];
                    }
                }
            }
        } else {
            // If SuperTable is not yet installed but we can find linkit that need updating lets update them in the db.
            $superTableLinkitFields = (new Query())
                ->select(['uid', 'settings'])
                ->from(['{{%fields}}'])
                ->where([
                    'and',
                    ['like', 'context', 'superTableBlockType'],
                    ['in', 'type', ['FruitLinkIt']],
                ])
                ->all();

            foreach ($superTableLinkitFields as $superTableLinkitField) {
                $fieldConfigsToMigrate[$superTableLinkitField['uid']] = [
                    'configPath' => false,
                    'config' => [
                        'settings' => Json::decode($superTableLinkitField['settings']),
                    ],
                ];
            }
        }

        // Migrate Fields
        if ($fieldConfigsToMigrate) {
            foreach ($fieldConfigsToMigrate as $fieldUid => $fieldConfig) {
                $type = LinkitField::class;
                $settings = $this->_migrateFieldSettings($fieldConfig['config']['settings'] ?? false);

                $fieldConfig['config']['type'] = $type;
                $fieldConfig['config']['settings'] = $settings;

                $this->update('{{%fields}}', [
                    'type' => $type,
                    'settings' => Json::encode($settings),
                ], ['uid' => $fieldUid]);

                if ($fieldConfig['configPath']) {
                    $projectConfig->set($fieldConfig['configPath'], $fieldConfig['config']);
                }
            }
        }

        $projectConfig->muteEvents = false;
    }

    private function _migrateFieldSettings($oldSettings): ?array
    {
        if (!$oldSettings) {
            return null;
        }

        $linkitField = new LinkitField();

        $newSettings = $linkitField->getSettings();
        $newSettings['defaultText'] = $oldSettings['defaultText'] ?? '';
        $newSettings['allowTarget'] = $oldSettings['allowTarget'] ?? 0;
        $newSettings['allowCustomText'] = $oldSettings['allowCustomText'] ?? 0;

        if ($oldSettings['types'] ?? false) {
            foreach ($oldSettings['types'] as $oldType) {
                switch ($oldType) {
                    case 'email':
                        $newSettings['types'][EmailLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                        ];
                        break;

                    case 'custom':
                        $newSettings['types'][UrlLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                        ];
                        break;

                    case 'tel':
                        $newSettings['types'][PhoneLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                        ];
                        break;

                    case 'entry':
                        $newSettings['types'][EntryLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(EntryLink::class, $oldSettings['entrySources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['entrySelectionLabel'] ?? '',
                        ];
                        break;

                    case 'category':
                        $categorySources = $oldSettings['categorySources'] ?? '*';
                        $newSettings['types'][CategoryLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(CategoryLink::class, $oldSettings['categorySources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['categorySelectionLabel'] ?? '',
                        ];
                        break;

                    case 'asset':
                        $newSettings['types'][AssetLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(AssetLink::class, $oldSettings['assetSources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['assetSelectionLabel'] ?? '',
                        ];
                        break;

                    case 'asset':
                        $newSettings['types'][UserLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(UserLink::class, $oldSettings['assetSources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['assetSelectionLabel'] ?? '',
                        ];
                        break;

                    case 'product':
                        $newSettings['types'][ProductLink::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(ProductLink::class, $oldSettings['productSources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['entrySelectionLabel'] ?? '',
                        ];
                        break;
                }
            }
        }
        return $newSettings;
    }

    public function safeDown(): bool
    {
        return true;
    }


    private function _sourcesToUids($elementType, $sources)
    {
        if ($sources == '*' || !is_array($sources)) {
            return $sources;
        }

        $newSources = [];
        foreach ($sources as $source) {
            $uid = false;
            $sourceKeyParts = explode(':', $source);
            $sourceId = $sourceKeyParts[1] ?? false;

            if ($sourceId && ctype_digit($sourceId)) {
                switch ($elementType) {
                    case CategoryLink::class:
                        $uid = Craft::$app->getCategories()->getGroupById($sourceId)->uid ?? false;
                        break;

                    case EntryLink::class:
                        $uid = Craft::$app->getSections()->getSectionById($sourceId)->uid ?? false;
                        break;

                    case UserLink::class:
                        $uid = Craft::$app->getUserGroups()->getGroupById($sourceId)->uid ?? false;
                        break;

                    case AssetLink::class:
                        $uid = Craft::$app->getAssets()->getFolderById($sourceId)->uid ?? false;
                        break;

                    case ProductLink::class:
                        if (Linkit::$commerceInstalled) {
                            $uid = CommercePlugin::getInstance()->getProducts()->getProductById($sourceId)->uid ?? false;
                        }
                        break;
                }

                $newSources[] = $uid ? ($sourceKeyParts[0] . ':' . $uid) : $source;
            }
        }
        return $newSources;
    }
}
