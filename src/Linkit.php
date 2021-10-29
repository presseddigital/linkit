<?php
namespace presseddigital\linkit;

use presseddigital\linkit\fields\LinkitField;
use presseddigital\linkit\services\LinkitService;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\base\FieldInterface;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\DefineEagerLoadingMapEvent;
use craft\events\EagerLoadElementsEvent;
use craft\services\Plugins;
use craft\services\Elements;
use craft\services\Fields;
use craft\helpers\ArrayHelper;
use craft\commerce\Plugin as CommercePlugin;
use yii\base\Event;

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

        Event::on(Fields::className(), Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $e) {
            $e->types[] = LinkitField::class;
        });

        Event::on(Element::class, Element::EVENT_DEFINE_EAGER_LOADING_MAP, function (DefineEagerLoadingMapEvent $e) {

            // ray([
            //     'DefineEagerLoadingMapEvent' => $e,
            //     'context' => Craft::$app->getContent()->fieldContext,
            // ]);

            list($handle, $elementLinkTypeHandle) = array_pad(explode(':', $e->handle), 2, false);
            if($elementLinkTypeHandle)
            {
                $field = Craft::$app->getFields()->getFieldByHandle($handle);
                if ($field && $field instanceof LinkitField)
                {
                    $map = [];
                    foreach ($e->sourceElements as $element)
                    {
                        $link = $element->$handle;
                        if($link && $link->getTypeHandle() === $elementLinkTypeHandle)
                        {
                            $map[] = [ 'source' => (int)$element->id, 'target' => (int)$link->value ];
                        }
                    }

                    if($map)
                    {
                        $linkType = self::$plugin->service->getLinkTypeByHandle($elementLinkTypeHandle);

                        $e->elementType = $linkType::elementType();
                        $e->map = $map;
                        $e->handled = true;
                    }
                }
            }

        });

        // Before any eager loading happens we need to update the handles to allow us to grab the data from our links
        // Ensure that there is a plan for each element type
        Event::on(Elements::class, Elements::EVENT_BEFORE_EAGER_LOAD_ELEMENTS, function (EagerLoadElementsEvent $e) {

            // ray([
            //     'EagerLoadElementsEvent' => $e,
            //     'context' => Craft::$app->getContent()->fieldContext,
            // ]);

            // Match fields, determine if in the with param, update plan handles and optionally add
            $allFields = Craft::$app->fields->getAllFields();

            // TODO - work out how we are going to handle fields outside of the global context, are they covered off in the nested plan param?

            // Linkit Field - Element Link
            //

            $linkitFields = ArrayHelper::index(
                ArrayHelper::where($allFields, function(FieldInterface $field) {
                    return $field instanceof LinkitField;
                }),
                'handle'
            );

            // Only update if it exists in the current with
            $elementLinkTypes = self::$plugin->service->getAvailableElementLinkTypes();

            foreach ($e->with as $i => $plan)
            {
                $linkitField = $linkitFields[$plan->handle] ?? false;
                if ($linkitField)
                {
                    // Remove the current plan
                    unset($e->with[$i]);

                    // Set a plan for all possible element link types
                    foreach($elementLinkTypes as $elementLinkType)
                    {
                        $elementLinkTypeHandle = $elementLinkType->getTypeHandle();
                        $clone = clone $plan;
                        $clone->handle .= ':'.$elementLinkTypeHandle;
                        $clone->alias .= ':'.$elementLinkTypeHandle;
                        $e->with[] = $clone;
                    }
                }
            }
        });

        Craft::info(Craft::t('linkit', '{name} plugin loaded', [ 'name' => $this->name ]), __METHOD__);
    }
}
