<?php

namespace presseddigital\linkit\models;

use craft\elements\Entry as CraftEntry;

use presseddigital\linkit\base\ElementLink;

class Entry extends ElementLink
{
    // Static
    // =========================================================================

    public static function elementType()
    {
        return CraftEntry::class;
    }

    // Public Methods
    // =========================================================================

    public function getEntry()
    {
        return $this->getElement();
    }
}

class_alias(Entry::class, \fruitstudios\linkit\models\Entry::class);
