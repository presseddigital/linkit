<?php
namespace fruitstudios\linkit\migrations;

use fruitstudios\linkit\migrations\Install;
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

class m180423_175007_linkit_craft2 extends Install
{

    public function safeUp()
    {
        $this->_upgradeFromCraft2();
        return true;
    }

    public function safeDown()
    {
        echo "m180423_175007_craft2 cannot be reverted.\n";
        return false;
    }
}
