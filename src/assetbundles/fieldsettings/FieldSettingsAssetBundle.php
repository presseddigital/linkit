<?php

namespace presseddigital\linkit\assetbundles\fieldsettings;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FieldSettingsAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@presseddigital/linkit/assetbundles/fieldsettings/build";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/LinkitFieldSettings.js',
        ];

        $this->css = [
            'css/styles.css',
        ];

        parent::init();
    }
}
