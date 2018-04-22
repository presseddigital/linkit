<?php
namespace fruitstudios\linkit\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Json;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\fields\LinkitField;
use fruitstudios\linkit\models\Email;
use fruitstudios\linkit\models\Phone;
use fruitstudios\linkit\models\Url;
use fruitstudios\linkit\models\Entry;
use fruitstudios\linkit\models\Category;
use fruitstudios\linkit\models\Asset;
use fruitstudios\linkit\models\Product;

class Install extends Migration
{
    public function safeUp()
    {
        if ($this->_upgradeFromCraft2()) {
            return;
        }
    }


    private function _upgradeFromCraft2()
    {
        // Locate and remove old linkit
        $row = (new \craft\db\Query())
            ->select(['id', 'settings'])
            ->from(['{{%plugins}}'])
            ->where(['in', 'handle', ['fruitlinkit', 'fruit-link-it', 'fruit-linkit']])
            ->one();

        if($row)
        {
            $this->delete('{{%plugins}}', ['id' => $row['id']]);
        }

        // Look for any old linkit fields and update their settings
        $fields = (new \craft\db\Query())
            ->select(['id', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['in', 'type', ['FruitLinkIt']])
            ->all();

        if($fields)
        {
            // Update field settings
            foreach($fields as $field)
            {
                $oldSettings = $field['settings'] ? Json::decode($field['settings']) : null;
                $newSettings = $this->_migrateFieldSettings($oldSettings);
                // Json::encode($newSettings);

                $this->update('{{%fields}}', [
                    'type' => LinkitField::class,
                    'settings' => Json::encode($newSettings)
                ], ['id' => $field['id']]);
            }
        }


        return true;
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

    private function _migrateFieldContent($oldContent)
    {
        if(!$oldContent)
        {
            return null;
        }

        $newContent = [];
        $newContent['customText'] = $oldSettings['customText'] ?? null;
        $newContent['target'] = $oldSettings['target'] ?? null;

        if($oldContent['type'])
        {
            switch ($oldContent['type'])
            {
                case 'email':
                    $newContent['type'] = Email::class;
                    $newContent['value'] = $oldContent['email'] ?? '';
                    break;

                case 'custom':
                    $newContent['type'] = Url::class;
                    $newContent['value'] = $oldContent['custom'] ?? '';
                    break;

                case 'tel':
                    $newContent['type'] = Phone::class;
                    $newContent['value'] = $oldContent['tel'] ?? '';
                    break;

                case 'entry':
                    $newContent['type'] = Entry::class;
                    $newContent['value'] = $oldContent['entry'] ?? '';
                    break;

                case 'category':
                    $newContent['type'] = Category::class;
                    $newContent['value'] = $oldContent['category'] ?? '';
                    break;

                case 'asset':
                    $newContent['type'] = Asset::class;
                    $newContent['value'] = $oldContent['asset'] ?? '';
                    break;

                case 'product':
                    $newContent['type'] = Product::class;
                    $newContent['value'] = $oldContent['product'] ?? '';
                    break;
            }
        }


        return $newContent;
    }

    public function safeDown()
    {

    }
}
