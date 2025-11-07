<?php

namespace presseddigital\linkit\console\controllers;

use Craft;
use craft\console\Controller;
use craft\db\Query;
use craft\fields\Link;
use craft\helpers\App;
use craft\helpers\Console;
use craft\helpers\Db;
use yii\console\ExitCode;
use presseddigital\linkit\fields\LinkitField;

class ConvertController extends Controller
{
//    public $defaultAction = 'convert';


    /**
     * Convert all LinkIt field to native Craft Link fields
     * @return int
     */
    public function actionIndex(): int
    {
        App::maxPowerCaptain();

        $this->stdout("Convert Linkit to native Link field.\n");

        // Get all LinkIt fields
        $fields = (new Query())
            ->from('{{%fields}}')
            ->where(['type' =>  LinkitField::class])
            ->all();

        // Loop through each field and migrate settings in this pass
        foreach ($fields as $field) {
            $this->stdout("\nPreparing to migrate field '{$field['handle']}' ({$field['uid']}) settings.\n");
            $linkItSettings = json_decode($field['settings'], true);
            $settings = $this->convertSettings($linkItSettings);

            // Update the field type and settings directly in the database
            Db::update(
                '{{%fields}}',
                [
                    'type' => Link::class,
                    'settings' => json_encode($settings, JSON_UNESCAPED_SLASHES)
                ],
                ['uid' => $field['uid']]
            );

            $this->stdout("    > Field {$field['handle']} successfully updated.\n");

            // Rebuild project config for this field
            $projectConfig = Craft::$app->getProjectConfig();
            $fieldsService = Craft::$app->getFields();

            // Refresh fields cache so we get the updated field
            $fieldsService->refreshFields();

            // Get the updated field object
            $updatedField = $fieldsService->getFieldByUid($field['uid']);

            if ($updatedField) {
                // Rebuild the project config for this field
                $projectConfig->set(
                    "fields.{$field['uid']}",
                    $fieldsService->createFieldConfig($updatedField)
                );

                $this->stdout("    > Project config rebuilt for field {$field['handle']}.\n");
            }
        }
        $this->stdout("\nConversion of LinkIt fields to Link complete.\n", Console::FG_GREEN, Console::BOLD );
        $this->stdout("Commit your project config changes, and run `craft up` on other environments for the changes to take effect.\n");
        $this->stdout("Run 'craft linkit/migrate' in this and other environments to migrate the content.\n\n");
        return ExitCode::OK;

    }


    /**
     * Convert LinkIt settings structure to native Link field settings structure
     *
     * @param array $linkitSettings
     * @return array
     */
    private function convertSettings(array $linkitSettings): array
    {
        $typeMapping = [
            'presseddigital\\linkit\\models\\Entry' => 'entry',
            'presseddigital\\linkit\\models\\Asset' => 'asset',
            'presseddigital\\linkit\\models\\Category' => 'category',
            'presseddigital\\linkit\\models\\User' => 'user',
            'presseddigital\\linkit\\models\\Email' => 'email',
            'presseddigital\\linkit\\models\\Phone' => 'tel',
            'presseddigital\\linkit\\models\\Url' => 'url',
            // Social media types merge into URL
            'presseddigital\\linkit\\models\\Twitter' => 'url',
            'presseddigital\\linkit\\models\\Facebook' => 'url',
            'presseddigital\\linkit\\models\\Instagram' => 'url',
            'presseddigital\\linkit\\models\\LinkedIn' => 'url',
        ];

        $types = [];
        $typeSettings = [];
        $advancedFields = [];

        // Add target if allowTarget was enabled
        if (!empty($linkitSettings['allowTarget'])) {
            $advancedFields[] = 'target';
        }

        // Process each type from the old structure
        if (isset($linkitSettings['types']) && is_array($linkitSettings['types'])) {
            foreach ($linkitSettings['types'] as $oldTypeClass => $typeConfig) {
                // Skip if not enabled
                if (empty($typeConfig['enabled']) || $typeConfig['enabled'] === "") {
                    continue;
                }

                // Map to new type name
                if (!isset($typeMapping[$oldTypeClass])) {
                    continue;
                }

                $newTypeName = $typeMapping[$oldTypeClass];

                // Add to types array (avoiding duplicates for merged social types)
                if (!in_array($newTypeName, $types)) {
                    $types[] = $newTypeName;
                }

                // Configure type-specific settings
                switch ($newTypeName) {
                    case 'entry':
                        $typeSettings['entry'] = [
                            'sources' => $typeConfig['sources'] ?? '*',
                            'showUnpermittedSections' => '1',
                            'showUnpermittedEntries' => '1',
                        ];
                        break;

                    case 'asset':
                        $typeSettings['asset'] = [
                            'sources' => $typeConfig['sources'] ?? '*',
                            'allowedKinds' => '*',
                            'showUnpermittedVolumes' => '1',
                            'showUnpermittedFiles' => '1',
                        ];
                        break;

                    case 'category':
                        $typeSettings['category'] = [
                            'sources' => $typeConfig['sources'] ?? '*',
                        ];
                        break;

                    case 'url':
                        // Merge URL settings from Url type and social media types
                        if (!isset($typeSettings['url'])) {
                            $typeSettings['url'] = [
                                'allowRootRelativeUrls' => '0',
                                'allowAnchors' => '0',
                                'allowCustomSchemes' => '0',
                            ];
                        }

                        // If this is the main URL type, map its specific settings
                        if ($oldTypeClass === 'presseddigital\\linkit\\models\\Url') {
                            if (!empty($typeConfig['allowPaths'])) {
                                $typeSettings['url']['allowRootRelativeUrls'] = '1';
                            }
                            if (!empty($typeConfig['allowHash'])) {
                                $typeSettings['url']['allowAnchors'] = '1';
                            }
                            // Allow custom schemes if mailto or alias enabled
                            if (!empty($typeConfig['allowMailto']) || !empty($typeConfig['allowAlias'])) {
                                $typeSettings['url']['allowCustomSchemes'] = '1';
                            }
                        }
                        break;
                }
            }
        }

        // Build the new settings structure
        $newSettings = [
            'advancedFields' => $advancedFields,
            'fullGraphqlData' => true,
            'maxLength' => 255,
            'showLabelField' => !empty($linkitSettings['allowCustomText']),
            'typeSettings' => $typeSettings,
            'types' => $types,
        ];

        return $newSettings;
    }

}