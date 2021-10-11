<?php
namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\base\ElementLink;

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


        // if ($element)
        // {
        //     $eagerLoadingHandle = $this->getField()->handle.':element';
        //     if($element->hasEagerLoadedElements($eagerLoadingHandle))
        //     {
        //         $elements = $element->getEagerLoadedElements($eagerLoadingHandle);
        //         return $elements[0] ?? null;
        //     }
        // }

        // if($value instanceof ElementInterface)
        // {
        //     return $value;
        // }

        // $element = Craft::$app->getElements()->getElementById((int)$value, $this->elementType);
        // return $element ? $element : $value;




        if(is_null($this->_entry))
        {
            $this->_entry = Craft::$app->getEntries()->getEntryById((int) $this->value, $this->ownerElement->siteId ?? null);
        }
        return $this->_entry;
    }
}

class_alias(Entry::class, \fruitstudios\linkit\models\Entry::class);
