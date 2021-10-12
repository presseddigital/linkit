<?php
namespace presseddigital\linkit\models;

use presseddigital\linkit\base\ElementLink;

use Craft;
use craft\elements\Entry as CraftEntry;

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
