<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\Linkit;
use fruitstudios\linkit\base\ElementLink;

use craft\elements\Entry as CraftEntry;

class Entry extends ElementLink
{
    // Private
    // =========================================================================

    private $_entry;

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
        if(is_null($this->_entry))
        {
            $this->_entry = Craft::$app->getEntries()->getEntryById((int) $this->value, static::elementType(), $this->ownerElement->siteId ?? null);
        }
        return $this->_entry;
    }
}
