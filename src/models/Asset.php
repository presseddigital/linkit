<?php
namespace presseddigital\linkit\models;

use Craft;

use presseddigital\linkit\Linkit;
use presseddigital\linkit\base\ElementLink;

use craft\elements\Asset as CraftAsset;

class Asset extends ElementLink
{
    // Private
    // =========================================================================

    private $_asset;

    // Static
    // =========================================================================

    public static function elementType()
    {
        return CraftAsset::class;
    }

    // Public Methods
    // =========================================================================

    public function getText(): string
    {
        return $this->getCustomOrDefaultText() ?? $this->getAsset()->filename ?? $this->getUrl() ?? '';
    }

    public function getAsset()
    {
        if(is_null($this->_asset))
        {
            $this->_asset = Craft::$app->getAssets()->getAssetById((int) $this->value, $this->ownerElement->siteId ?? null);
        }
        return $this->_asset;
    }
}

class_alias(Asset::class, \fruitstudios\linkit\models\Asset::class);
