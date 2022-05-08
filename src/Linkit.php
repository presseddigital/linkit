<?php

namespace presseddigital\linkit;

use Craft;
use craft\base\Element;

use craft\base\FieldInterface;
use craft\base\Plugin;
use craft\commerce\Plugin as CommercePlugin;
use craft\events\DefineEagerLoadingMapEvent;
use craft\events\EagerLoadElementsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\ArrayHelper;
use craft\services\Elements;
use craft\services\Fields;
use presseddigital\linkit\fields\LinkitField;
use presseddigital\linkit\services\LinkitService;
use yii\base\Event;

class Linkit extends Plugin
{
    // Static Properties
    // =========================================================================

    public static $plugin;
    public static $commerceInstalled;

    // Public Methods
    // =========================================================================

    public string $schemaVersion = '1.2.0';

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;
        self::$commerceInstalled = class_exists(CommercePlugin::class);

        $this->setComponents([
            'service' => LinkitService::class,
        ]);

        Event::on(Fields::className(), Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $e): void {
            $e->types[] = LinkitField::class;
        });

        // Before any eager loading happens we need to update the handles to allow us to grab the data from our links
        // - Ensuring that there is a plan for each element link type
        Event::on(Elements::class, Elements::EVENT_BEFORE_EAGER_LOAD_ELEMENTS, function(EagerLoadElementsEvent $e): void {

            // Match fields, determine if in the with param, update plan handles
            // - Get from any context

            // - CAVEAT:
            // - There is a small chance that our nested eager loading logic could catch a field that is from a different context, but named the same and which might not actually be a linkit field.
            // All of these fields can exist in different contexts:
            //
            // - matrixBlockHandle:typeHandle:someFieldHandle
            // - matrixBlockHandle:anotherTypeHandle:someFieldHandle
            // - someFieldHandle
            //
            // There is a posibility (although unlikly) that one of these could be picked up as a linkit field when infact it is not, but it would have to be another field that supported eager loading and conflicted.
            // Only ways around we can think is that Craft provides the context when setting the eagerloading plans, or we use some kind of mapping to link up the fields and make sure we have the right one.

            $allFields = Craft::$app->fields->getAllFields(false);

            // $linkitFields = ArrayHelper::index(
            //     ArrayHelper::where($allFields, function(FieldInterface $field) {
            //         return $field instanceof LinkitField;
            //     }),
            //     'handle'
            // );

            $linkitFieldHandles = ArrayHelper::getColumn(
                ArrayHelper::where($allFields, fn(FieldInterface $field) => $field instanceof LinkitField),
                'handle'
            );

            // Only update if it exists in the current with
            $elementLinkTypes = self::$plugin->service->getAvailableElementLinkTypes();

            // Loop current plans (included any nested plans) and replace with our own custom plans
            $with = [];
            foreach ($e->with as $i => $plan) {
                array_push($with, ...$this->_getCustomPlans($plan, $linkitFieldHandles, $elementLinkTypes));

                // $isLinkitField = ArrayHelper::isIn($plan->handle, $linkitFieldHandles);
                // if ($isLinkitField)
                // {
                //     // Remove the current plan
                //     unset($e->with[$i]);
                //
                //     // Set a plan for all possible element link types
                //     foreach($elementLinkTypes as $elementLinkType)
                //     {
                //         $elementLinkTypeHandle = $elementLinkType->getTypeHandle();
                //         $clone = clone $plan;
                //         $handle = $clone->handle.'.'.$elementLinkTypeHandle;
                //         $clone->handle = $handle;
                //         $clone->alias = $handle;
                //         $with[] = $clone;
                //     }
                // }
            }

            $e->with = $with;
        });

        Event::on(Element::class, Element::EVENT_DEFINE_EAGER_LOADING_MAP, function(DefineEagerLoadingMapEvent $e): void {
            [$handle, $elementLinkTypeHandle] = array_pad(explode('.', $e->handle), 2, false);
            if ($elementLinkTypeHandle) {
                $field = Craft::$app->getFields()->getFieldByHandle($handle);
                if ($field && $field instanceof LinkitField) {
                    $map = [];
                    foreach ($e->sourceElements as $element) {
                        $link = $element->$handle;
                        if ($link && $link->getTypeHandle() === $elementLinkTypeHandle) {
                            $map[] = [ 'source' => (int)$element->id, 'target' => (int)$link->value ];
                        }
                    }

                    if ($map) {
                        $linkType = self::$plugin->service->getLinkTypeByHandle($elementLinkTypeHandle);

                        $e->elementType = $linkType::elementType();
                        $e->map = $map;
                        $e->handled = true;
                    }
                }
            }
        });

        Craft::info(Craft::t('linkit', '{name} plugin loaded', [ 'name' => $this->name ]), __METHOD__);
    }

    /**
     * @return mixed[]
     */
    private function _getCustomPlans($plan, $linkitFieldHandles, $elementLinkTypes): array
    {
        $newPlans = [];

        $handleParts = explode(':', $plan->handle);
        $isLinkitField = ArrayHelper::isIn(end($handleParts), $linkitFieldHandles);
        if ($isLinkitField) {
            // Set a plan for all possible element link types
            foreach ($elementLinkTypes as $elementLinkType) {
                $elementLinkTypeHandle = $elementLinkType->getTypeHandle();

                $clone = clone $plan;
                $handle = $clone->handle . '.' . $elementLinkTypeHandle;

                $clone->handle = $handle;
                $clone->alias = $handle;

                $newPlans[] = $clone;
            }
        } else {
            $newPlans = [$plan];
        }

        if (!empty($plan->nested)) {
            foreach ($newPlans as &$newPlan) {
                $newNestedPlans = [];
                foreach ($newPlan->nested as $nestedPlan) {
                    array_push($newNestedPlans, ...$this->_getCustomPlans($nestedPlan, $linkitFieldHandles, $elementLinkTypes));
                }
                $newPlan->nested = $newNestedPlans;
            }
        }

        return $newPlans;
    }
}
