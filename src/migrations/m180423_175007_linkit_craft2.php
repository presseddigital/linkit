<?php

namespace presseddigital\linkit\migrations;

class m180423_175007_linkit_craft2 extends Install
{
    public function safeUp()
    {
        $this->upgradeFromCraft2();
        return true;
    }

    public function safeDown()
    {
        echo "m180423_175007_craft2 cannot be reverted.\n";
        return false;
    }
}
