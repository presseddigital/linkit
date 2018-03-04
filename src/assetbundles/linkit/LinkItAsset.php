<?php
/**
 * Link It plugin for Craft CMS 3.x
 *
 * One link field to rule them all...
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2017 Fruit Studios
 */

namespace fruitstudios\linkit\assetbundles\LinkIt;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * LinkItAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    Fruit Studios
 * @package   LinkIt
 * @since     1.0.0
 */
class LinkItAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@fruitstudios/linkit/assetbundles/linkit/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/LinkIt.js',
        ];

        $this->css = [
            'css/LinkIt.css',
        ];

        parent::init();
    }
}
