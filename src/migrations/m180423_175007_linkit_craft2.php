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
use craft\helpers\Json;
use craft\services\Fields;
use craft\services\Plugins;

class m180423_175007_linkit_craft2 extends Migration
{

    public function safeUp()
    {
        $this->_upgradeFromCraft2();
    }

    private function _upgradeFromCraft2()
    {
        // Get Project Config
        $projectConfig = Craft::$app->getProjectConfig();
        $projectConfig->muteEvents = true;

        // Don't make the same config changes twice
        $schemaVersion = $projectConfig->get('plugins.linkit.schemaVersion', true);
        if (version_compare($schemaVersion, '3.1.8', '>='))
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
                    $this->delete('{{%plugins}}', ['handle' => $pluginHandle]);
                    $projectConfig->remove(Plugins::CONFIG_PLUGINS_KEY . '.' . $pluginHandle);
                    break;
            }
        }


        // Get the field data from the project config
        $fields = $projectConfig->get(Fields::CONFIG_FIELDS_KEY) ?? [];
        foreach ($fields as $fieldUid => $field)
        {
            if ($field['type'] === 'FruitLinkIt')
            {
                $type = LinkitField::class;
                $settings = $this->_migrateFieldSettings($field['settings'] ? Json::decode($field['settings']) : null);

                $field['type'] = $type;
                $field['settings'] = $settings;

                $this->update('{{%fields}}', [
                    'type' => $type,
                    'settings' => Json::encode($settings),
                ], ['uid' => $fieldUid]);
                $projectConfig->set(Fields::CONFIG_FIELDS_KEY . '.' . $fieldUid, $field);
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

        if($oldSettings['types'])
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
                            'sources' => $oldSettings['entrySources'] ?? '*',
                            'customSelectionLabel' => $oldSettings['entrySelectionLabel'] ?? '',
                        ];
                        break;

                    case 'category':
                        $newSettings['types'][Category::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $oldSettings['categorySources'] ?? '*',
                            'customSelectionLabel' => $oldSettings['categorySelectionLabel'] ?? '',
                        ];
                        break;

                    case 'asset':
                        $newSettings['types'][Asset::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $oldSettings['assetSources'] ?? '*',
                            'customSelectionLabel' => $oldSettings['assetSelectionLabel'] ?? '',
                        ];
                        break;

                    case 'product':
                        $newSettings['types'][Product::class] = [
                            'enabled' => 1,
                            'customLabel' => null,
                            'sources' => $oldSettings['entrySources'] ?? '*',
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
        echo "m180423_175007_craft2 cannot be reverted.\n";
        return false;
    }
}
