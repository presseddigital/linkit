<?php
namespace fruitstudios\linkit\migrations;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\fields\LinkitField;
use fruitstudios\linkit\models\Email;
use fruitstudios\linkit\models\Phone;
use fruitstudios\linkit\models\Url;
use fruitstudios\linkit\models\Entry;
use fruitstudios\linkit\models\Category;
use fruitstudios\linkit\models\Asset;
use fruitstudios\linkit\models\Product;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\Db;
use craft\services\Fields;
use craft\services\Plugins;

use craft\commerce\Plugin as CommercePlugin;

class Install extends Migration
{
    public function safeUp()
    {
        $this->upgradeFromCraft2();
        return true;
    }

    protected function upgradeFromCraft2()
    {
        // Get Project Config
        $projectConfig = Craft::$app->getProjectConfig();
        $projectConfig->muteEvents = true;

        // Don't make the same config changes twice
        $schemaVersion = $projectConfig->get('plugins.linkit.schemaVersion', true);
        if ($schemaVersion && version_compare($schemaVersion, '1.0.8', '>='))
        {
            return;
        }

        // Locate and remove old linkit
        $plugins = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY) ?? [];
        foreach ($plugins as $pluginHandle => $pluginData)
        {
            switch ($pluginHandle)
            {
                case 'fruitlinkit':
                case 'fruit-link-it':
                case 'fruit-linkit':
                    $projectConfig->remove(Plugins::CONFIG_PLUGINS_KEY . '.' . $pluginHandle);
                    break;
            }
        }
        $this->delete('{{%plugins}}', ['handle' => ['fruitlinkit', 'fruit-linkit', 'fruit-link-it']]);

        // Get the field data from the project config
        $fieldConfigs = $projectConfig->get(Fields::CONFIG_FIELDS_KEY) ?? [];
        $fieldConfigsToMigrate = [];
        foreach ($fieldConfigs as $fieldUid => $fieldConfig)
        {
            if(isset($fieldConfig['type']) && $fieldConfig['type'] === 'FruitLinkIt')
            {
                $fieldConfigsToMigrate[$fieldUid] = [
                    'configPath' => Fields::CONFIG_FIELDS_KEY.'.'.$fieldUid,
                    'config' => $fieldConfig
                ];
            }
        }

        // Migrate Matrix
        $matrixBlockTypeConfigs = $projectConfig->get('matrixBlockTypes') ?? [];
        foreach ($matrixBlockTypeConfigs as $matrixBlockTypeUid => $matrixBlockTypeConfig)
        {
            $fieldConfigs = $matrixBlockTypeConfig['fields'] ?? [];
            foreach ($fieldConfigs as $fieldUid => $fieldConfig)
            {
                if(isset($fieldConfig['type']) && $fieldConfig['type'] === 'FruitLinkIt')
                {
                    $fieldConfigsToMigrate[$fieldUid] = [
                        'configPath' => 'matrixBlockTypes.'.$matrixBlockTypeUid.'.fields.'.$fieldUid,
                        'config' => $fieldConfig
                    ];
                }
            }
        }

        // Migrate SuperTable
        $superTableBlockTypeConfigs = $projectConfig->get('superTableBlockTypes') ?? [];
        if($superTableBlockTypeConfigs)
        {
            foreach ($superTableBlockTypeConfigs as $superTableBlockTypeUid => $superTableBlockTypeConfig)
            {
                $fieldConfigs = $superTableBlockTypeConfig['fields'] ?? [];
                foreach ($fieldConfigs as $fieldUid => $fieldConfig)
                {
                    if(isset($fieldConfig['type']) && $fieldConfig['type'] === 'FruitLinkIt')
                    {
                        $fieldConfigsToMigrate[$fieldUid] = [
                            'configPath' => 'superTableBlockTypes.'.$superTableBlockTypeUid.'.fields.'.$fieldUid,
                            'config' => $fieldConfig
                        ];
                    }
                }
            }
        }
        else
        {
            // If SuperTable is not yet installed but we can find linkit that need updating lets update them in the db.
            $superTableLinkitFields = (new Query())
                ->select(['uid', 'settings'])
                ->from(['{{%fields}}'])
                ->where([
                    'and',
                    ['like', 'context', 'superTableBlockType'],
                    ['in', 'type', ['FruitLinkIt']]
                ])
                ->all();

            foreach ($superTableLinkitFields as $superTableLinkitField)
            {
                $fieldConfigsToMigrate[$superTableLinkitField['uid']] = [
                    'configPath' => false,
                    'config' => [
                        'settings' => Json::decode($superTableLinkitField['settings']),
                    ]
                ];
            }

        }

        // Migrate Fields
        if($fieldConfigsToMigrate)
        {
            foreach ($fieldConfigsToMigrate as $fieldUid => $fieldConfig)
            {
                $type = LinkitField::class;
                $settings = $this->_migrateFieldSettings($fieldConfig['config']['settings'] ?? false);

                $fieldConfig['config']['type'] = $type;
                $fieldConfig['config']['settings'] = $settings;

                $this->update('{{%fields}}', [
                    'type' => $type,
                    'settings' => Json::encode($settings),
                ], ['uid' => $fieldUid]);

                if($fieldConfig['configPath'])
                {
                    $projectConfig->set($fieldConfig['configPath'], $fieldConfig['config']);
                }
            }
        }

        $projectConfig->muteEvents = false;
    }

    private function _migrateFieldSettings($oldSettings)
    {
        if(!$oldSettings)
        {
            return null;
        }

        $linkitField = new LinkitField();

        $newSettings = $linkitField->getSettings();
        $newSettings['defaultText'] = $oldSettings['defaultText'] ?? '';
        $newSettings['allowTarget'] = $oldSettings['allowTarget'] ?? 0;
        $newSettings['allowCustomText'] = $oldSettings['allowCustomText'] ?? 0;

        if($oldSettings['types'] ?? false)
        {
            foreach ($oldSettings['types'] as $oldType)
            {
                switch ($oldType)
                {
                    case 'email':
                        $newSettings['types'][Email::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                        ];
                        break;

                    case 'custom':
                        $newSettings['types'][Url::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                        ];
                        break;

                    case 'tel':
                        $newSettings['types'][Phone::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                        ];
                        break;

                    case 'entry':
                        $newSettings['types'][Entry::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(Entry::class, $oldSettings['entrySources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['entrySelectionLabel'] ?? '',
                        ];
                        break;

                    case 'category':
                        $categorySources = $oldSettings['categorySources'] ?? '*';
                        $newSettings['types'][Category::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(Category::class, $oldSettings['categorySources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['categorySelectionLabel'] ?? '',
                        ];
                        break;

                    case 'asset':
                        $newSettings['types'][Asset::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(Asset::class, $oldSettings['assetSources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['assetSelectionLabel'] ?? '',
                        ];
                        break;

                    case 'product':
                        $newSettings['types'][Product::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $this->_sourcesToUids(Product::class, $oldSettings['productSources'] ?? '*'),
                            'customSelectionLabel' => $oldSettings['entrySelectionLabel'] ?? '',
                        ];
                        break;
                }
            }
        }
        return $newSettings;
    }

    public function safeDown()
    {
        return true;
    }


    private function _sourcesToUids($elementType, $sources)
    {
        if($sources == '*' || !is_array($sources))
        {
            return $sources;
        }

        $newSources = [];
        foreach($sources as $source)
        {
            $uid = false;
            $sourceKeyParts = explode(':', $source);
            $sourceId = $sourceKeyParts[1] ?? false;

            if($sourceId && ctype_digit($sourceId))
            {
                switch ($elementType)
                {
                    case Category::class:
                        $uid = Craft::$app->getCategories()->getGroupById($sourceId)->uid ?? false;
                        break;

                    case Entry::class:
                        $uid = Craft::$app->getSections()->getSectionById($sourceId)->uid ?? false;
                        break;

                    case User::class:
                        $uid = Craft::$app->getUserGroups()->getGroupById($sourceId)->uid ?? false;
                        break;

                    case Asset::class:
                        $uid = Craft::$app->getAssets()->getFolderById($sourceId)->uid ?? false;
                        break;

                    case Product::class:
                        if (Linkit::$commerceInstalled)
                        {
                            $uid = CommercePlugin::getInstance()->getProducts()->getProductById($sourceId)->uid ?? false;
                        }

                        break;
                }

                $newSources[] = $uid ? ($sourceKeyParts[0].':'.$uid) : $source;
            }
        }
        return $newSources;
    }

}


