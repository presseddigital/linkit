<?php
namespace ns\prefix\migrations;

use craft\db\Migration;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\fields\LinkitField;

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
        // Old linkit
        $row = (new \craft\db\Query())
            ->select(['id', 'settings'])
            ->from(['{{%plugins}}'])
            ->where(['in', 'handle', ['fruitlinkit', 'fruit-link-it', 'fruit-linkit']])
            ->one();

        if (!$row) {
            return false;
        }

        // Delete the old plugin row
        //$this->delete('{{%plugins}}', ['id' => $row['id']]);


        // Linkit felds
        $fields = (new \craft\db\Query())
            ->select(['id', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['in', 'type', ['FruitLinkIt']])
            ->all();

        // $this->update('{{%fields}}', [
        //     'type' => LinkitField::class
        // ], ['type' => 'FruitLinkIt']);




        return true;
    }

    public function safeDown()
    {

    }
}
