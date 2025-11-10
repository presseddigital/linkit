<?php

namespace presseddigital\linkit\console\controllers;

use Craft;
use craft\console\Controller;
use craft\db\Query;
use craft\fields\Link;
use craft\helpers\App;
use craft\helpers\Db;
use presseddigital\linkit\fields\LinkitField;
use yii\console\ExitCode;
use craft\helpers\Console;

class MigrateController extends Controller
{
    /**
     * Migrate all LinkIt field content to native Craft Link fields
     * @return int
     */

    public function actionIndex(): int
    {
        $fields = (new Query())
            ->from('{{%fields}}')
            ->where(['type' => Link::class])
            ->all();

        foreach ($fields as $field) {
            $this->stdout("\nPreparing to migrate field '{$field['handle']}' ({$field['uid']}) content.\n");

            $fieldLayouts = (new Query())
                ->from('{{%fieldlayouts}}')
                ->where(['like', 'config', $field['uid']])
                ->all();

            foreach($fieldLayouts as $fieldLayout) {
                $element = $this->findField(json_decode($fieldLayout['config'], true), $field['uid']);

                if ($element){
                    $contentEntries = (new Query())
                        ->from('{{%elements_sites}}')
                        ->where(['like', 'content', $element['uid']])
                        ->all();

                    if (count($contentEntries) < 1) {
                        $this->stdout("    > No content to migrate for field '{$field['handle']}'\n", Console::FG_YELLOW);
                        continue;
                    }

                    foreach($contentEntries as $contentEntry) {
                        $newContent = $this->convertContent($contentEntry['content'], $contentEntry['siteId'], $element['uid'] );
                        if ($newContent !== false){
                            Db::update('{{%elements_sites}}',
                                ['content' => $newContent],
                                [
                                    'elementId' => $contentEntry['elementId'],
                                    'siteId' => $contentEntry['siteId']
                                ]);
                            $this->stdout("    > Migrated content for element #{$contentEntry['elementId']}\n");
                        } else {
                            $this->stdout("    > Unable to convert content for element #{$contentEntry['elementId']}\n", Console::FG_RED);
                        }
                    }
                }
            }
            $this->stdout("    > Field '{$field['handle']}' content migrated.\n", Console::FG_GREEN);
        }

        $this->stdout("\nLinkIt content conversion is complete.\n", Console::FG_GREEN);

        return ExitCode::OK;
    }

    /**
     * Find and return the matching field within the included $data
     *
     * @param $data The JSON data to find the field in
     * @param $fieldUid the field UID to find
     * @return false|mixed The found field or false if not found
     */

    private function findField($data, $fieldUid){
        $foundElement = false;

        foreach ($data['tabs'] as &$tab) {
            foreach ($tab['elements'] as &$element) {
                if (isset($element['fieldUid']) && $element['fieldUid'] === $fieldUid) {
                    $foundElement = &$element;
                    break 2; // Break out of loop
                }
            }
        }

        return $foundElement;
    }

    /**
     * Convert LinkIt content format to native Link field format
     *
     * @param string $contentJson The JSON string from the content column
     * @param string $fieldUid The field UID to update
     * @return string The converted JSON string
     */
    private function convertContent(string $contentJson, int $siteId, string $fieldUid): array|false
    {
        $content = json_decode($contentJson, true);

        if (!$content || !isset($content[$fieldUid])) {
            return false;
        }

        $linkitData = $content[$fieldUid];

        // If it's already been converted, return
        if (isset($linkitData['type']) && str_starts_with($linkitData['type'], 'presseddigital') === false){
            return false;
        }

        // If it's not valid LinkIt data, return
        if (!$linkitData || !isset($linkitData['type']) || !isset($linkitData['value'])) {
            return false;
        }

        $typeMapping = [
            'presseddigital\\linkit\\models\\Entry' => 'entry',
            'presseddigital\\linkit\\models\\Asset' => 'asset',
            'presseddigital\\linkit\\models\\Category' => 'category',
            'presseddigital\\linkit\\models\\User' => 'user',
            'presseddigital\\linkit\\models\\Email' => 'email',
            'presseddigital\\linkit\\models\\Phone' => 'tel',
            'presseddigital\\linkit\\models\\Url' => 'url',
            'presseddigital\\linkit\\models\\Twitter' => 'url',
            'presseddigital\\linkit\\models\\Facebook' => 'url',
            'presseddigital\\linkit\\models\\Instagram' => 'url',
            'presseddigital\\linkit\\models\\LinkedIn' => 'url',
        ];

        // Map the type
        $oldType = $linkitData['type'];
        $newType = $typeMapping[$oldType] ?? 'url';

        // Build the new Link field structure
        $newLinkData = [
            'type' => $newType,
            'label' => $linkitData['customText'] ?? null,
            'value' => $this->convertValueForType($newType, $linkitData['value'], $siteId),
        ];

        // Add target if present
        if (!empty($linkitData['target'])) {
            $newLinkData['target'] = $linkitData['target'];
        }

        // Update the content with the new format
        $content[$fieldUid] = $newLinkData;

//        return json_encode($content, JSON_UNESCAPED_SLASHES );
        return $content;
    }

    /**
     * Convert value based on type - element types need special reference format
     *
     * @param string $type The new type (entry, asset, category, etc.)
     * @param mixed $value The original value
     * @return string The converted value
     */
    private function convertValueForType(string $type, $value, int $siteId): string
    {
        // Element types need the special {type:id@siteId:url} format
        switch ($type) {
            case 'entry':
                return "{{entry:{$value}@{$siteId}:url}}";
            case 'asset':
                return "{{asset:{$value}@{$siteId}:url}}";
            case 'category':
                return "{{category:{$value}@{$siteId}:url}}";
            case 'user':
                return "{{user:{$value}@{$siteId}:url}}";
            case 'email':
                return "mailto:{$value}";
            case 'tel':
                return "tel:{$value}";
            default:
                // For URL and other types, return as-is
                return (string) $value;
        }
    }
}