<?php
namespace fruitstudios\linkit;

use fruitstudios\linkit\fields\LinkitField;
use fruitstudios\linkit\services\LinkitService;

use Craft;
use craft\base\Plugin;
use yii\base\Event;

use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;

use craft\services\Plugins;
use craft\services\Fields;


class Linkit extends Plugin
{
    // Static Properties
    // =========================================================================

    public static $plugin;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->setComponents([
            'service' => LinkitService::class,
        ]);

        Event::on(Fields::className(), Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = LinkitField::class;
        });

        Event::on(Plugins::className(), Plugins::EVENT_AFTER_INSTALL_PLUGIN, function (PluginEvent $event) {
            if ($event->plugin === $this)
            {

            }
        });

        // Event::on(Linkit::className(), Linkit::EVENT_REGISTER_LINKIT_LINK_TYPES, function (RegisterLinkTypesEvent $event) {
        //     $event->types[] = new SomeCustomClass();
        // });

        Craft::info(
            Craft::t('linkit', '{name} plugin loaded', [
                'name' => $this->name
            ]),
            __METHOD__
        );
    }
}
