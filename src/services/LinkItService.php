<?php
/**
 * Link It plugin for Craft CMS 3.x
 *
 * One link field to rule them all...
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2017 Fruit Studios
 */

namespace fruitstudios\linkit\services;

use Craft;
use craft\base\Component;

use fruitstudios\linkit\LinkIt;
use fruitstudios\linkit\types\LinkTypeModel;
use fruitstudios\linkit\types\Phone;
use fruitstudios\linkit\types\Url;
use fruitstudios\linkit\types\Email;
use fruitstudios\linkit\types\Asset;
use fruitstudios\linkit\types\Entry;
use fruitstudios\linkit\types\Category;
use fruitstudios\linkit\types\User;
// use fruitstudios\linkit\types\Product;



/**
 * LinkItService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Fruit Studios
 * @package   LinkIt
 * @since     1.0.0
 */
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

        // Element link types
        $linkTypes[] = new Entry();
        $linkTypes[] = new Category();
        $linkTypes[] = new Asset();
        $linkTypes[] = new User();

        // Product link
        // $linkTypes[] = new Product();

        // TODO: Register any third party link types here

        return $linkTypes;
    }

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
