<?php
namespace fruitstudios\linkit\services;

use Craft;
use craft\base\Component;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\models\Phone;
use fruitstudios\linkit\models\Url;
use fruitstudios\linkit\models\Email;
use fruitstudios\linkit\models\Asset;
use fruitstudios\linkit\models\Entry;
use fruitstudios\linkit\models\Category;
use fruitstudios\linkit\models\User;
use fruitstudios\linkit\models\Product;
use fruitstudios\linkit\models\Twitter;
use fruitstudios\linkit\models\Facebook;
use fruitstudios\linkit\models\LinkedIn;
use fruitstudios\linkit\models\Instagram;

class LinkItService extends Component
{
    // Public Methods
    // =========================================================================

    public function getAvailableLinkTypes()
    {
        $linkTypes = [];

        // Basic link types
        $linkTypes[] = new Email();
        $linkTypes[] = new Phone();
        $linkTypes[] = new Url();

        // Social link types
        $linkTypes[] = new Twitter();
        $linkTypes[] = new Facebook();
        $linkTypes[] = new Instagram();
        $linkTypes[] = new LinkedIn();

        // Element link types
        $linkTypes[] = new Entry();
        $linkTypes[] = new Category();
        $linkTypes[] = new Asset();
        $linkTypes[] = new User();

        // Product link
        // try{
        //     Craft::createObject('craft\commerce\Plugin');
        //     $linkTypes[] = new Product();
        // } catch(ErrorException $exception) {
        //     //$error = $exception->getMessage();
        // }

        return $linkTypes;

        // // Third Party
        // $event = new RegisterComponentTypesEvent([
        //     'types' => $fieldTypes
        // ]);
        // $this->trigger(self::EVENT_REGISTER_LINKIT_FIELD_TYPES, $event);

        // return $event->types;
    }

    // Thrid Party Field Types
    //
    // public function getAllFieldTypes(): array
    // {
    //     $fieldTypes = [
    //         AssetsField::class,
    //         CategoriesField::class,
    //         CheckboxesField::class,
    //         ColorField::class,
    //         DateField::class,
    //         DropdownField::class,
    //         EmailField::class,
    //         EntriesField::class,
    //         LightswitchField::class,
    //         MatrixField::class,
    //         MultiSelectField::class,
    //         NumberField::class,
    //         PlainTextField::class,
    //         RadioButtonsField::class,
    //         TableField::class,
    //         TagsField::class,
    //         UrlField::class,
    //         UsersField::class,
    //     ];

    //     $event = new RegisterComponentTypesEvent([
    //         'types' => $fieldTypes
    //     ]);
    //     $this->trigger(self::EVENT_REGISTER_FIELD_TYPES, $event);

    //     return $event->types;
    // }



    public function getSourceOptions($elementType): array
    {
        $sources = Craft::$app->getElementIndexes()->getSources($elementType, 'modal');
        $options = [];
        $optionNames = [];

        foreach ($sources as $source) {
            // Make sure it's not a heading
            if (!isset($source['heading'])) {
                $options[] = [
                    'label' => $source['label'],
                    'value' => $source['key']
                ];
                $optionNames[] = $source['label'];
            }
        }

        // Sort alphabetically
        array_multisort($optionNames, SORT_NATURAL | SORT_FLAG_CASE, $options);

        return $options;
    }



}
