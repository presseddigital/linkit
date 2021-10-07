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
