<?php

namespace presseddigital\linkit\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201204_090026_pressed_transfer migration.
 */
class m201204_090026_pressed_transfer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Don’t make the same config changes twice
        $schemaVersion = Craft::$app->projectConfig->get('plugins.linkit.schemaVersion', true);

        if (version_compare($schemaVersion, '1.2.0', '<'))
        {
            $fieldsService = Craft::$app->getFields();
            foreach ($fieldsService->getAllFields() as $field)
            {
                if ($field instanceof \fruitstudios\linkit\fields\LinkitField)
                {
                    // Update the type settings
                    $fieldSettings = $field->getSettings();
                    $newTypes = [];
                    foreach($fieldSettings['types'] as $typeClass => $typeSettings)
                    {
                        $newTypes[str_replace('fruitstudios', 'presseddigital', $typeClass)] = $typeSettings;
                    }
                    $fieldSettings['types'] = $newTypes;

                    // Create field of new type
                    $field = $fieldsService->createField([
                        'type' => \fruitstudios\linkit\fields\LinkitField::class,
                        'id' => $field->id,
                        'uid' => $field->uid,
                        'groupId' => $field->groupId,
                        'name' => $field->name,
                        'handle' => $field->handle,
                        'columnSuffix' => $field->columnSuffix ?? null,
                        'instructions' => $field->instructions,
                        'searchable' => (bool)$field->searchable,
                        'translationMethod' => $field->translationMethod,
                        'translationKeyFormat' => $field->translationKeyFormat,
                        'settings' => $fieldSettings,
                    ]);

                    // Save new field
                    $fieldsService->saveField($field);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m201204_090026_pressed_transfer cannot be reverted.\n";
        return false;
    }
}
