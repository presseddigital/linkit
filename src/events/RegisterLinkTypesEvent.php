<?php

namespace presseddigital\linkit\events;

use yii\base\Event;

class RegisterLinkTypesEvent extends Event
{
    // Properties
    // =========================================================================

    public array $types = [];
}
