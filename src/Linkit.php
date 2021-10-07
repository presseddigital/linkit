<?php
namespace presseddigital\linkit;

use presseddigital\linkit\fields\LinkitField;
use presseddigital\linkit\services\LinkitService;

use Craft;
use craft\base\Plugin;
use yii\base\Event;

use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;

use craft\services\Plugins;
use craft\services\Fields;

use craft\commerce\Plugin as CommercePlugin;

class Linkit extends Plugin
{
    // Static Properties
    // =========================================================================

    public static $plugin;
    public static $commerceInstalled;


    // Public Methods
    // =========================================================================

    public $schemaVersion = '1.2.0';

    public function init()
    {
        parent::init();

        self::$plugin = $this;
        self::$commerceInstalled = class_exists(CommercePlugin::class);


        $this->setComponents([
            'service' => LinkitService::class,
        ]);

        Event::on(
            Fields::className(),
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LinkitField::class;
            }
        );

        Craft::info(
            Craft::t('linkit', '{name} plugin loaded', [
                'name' => $this->name
            ]),
            __METHOD__
        );
    }
}
