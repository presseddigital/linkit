<?php
namespace fruitstudios\linkit\events;

use yii\base\Event;

class RegisterLinkTypesEvent extends Event
{
    // Properties
    // =========================================================================

    public $types = [];
}
