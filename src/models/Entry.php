<?php
namespace fruitstudios\linkit\models;

use Craft;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\base\ElementLink;

use craft\elements\Entry as CraftEntry;

class Entry extends ElementLink
{
    // Private
    // =========================================================================

    private $_entry;

    // Public
    // =========================================================================

    // Static
    // =========================================================================

    public static function elementType()
    {
        return CraftEntry::class;
    }

    public static function settingsTemplatePath(): string
    {
        return 'linkit/types/settings/_element';
    }

    public static function inputTemplatePath(): string
    {
        return 'linkit/types/input/_element';
    }

    // Public Methods
    // =========================================================================

    // public function getUrl(): string
    // {
    //     if(!$this->getEntries())
    //     {
    //         return '';
    //     }
    //     return $this->getEntries()->getUrl() ?? '';
    // }

    // public function getText(): string
    // {
    //     if($this->customText != '')
    //     {
    //         return $this->customText;
    //     }
    //     return $this->getEntries()->title ?? $this->getUrl() ?? '';
    // }

    // public function getEntries()
    // {
    //     if(is_null($this->_entry))
    //     {
    //         $this->_entry = Craft::$app->getEntries()->getEntryById((int) $this->value);
    //     }
    //     return $this->_entry;
    // }
}
