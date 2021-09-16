<?php
namespace fruitstudios\linkit\gql\types;

use fruitstudios\linkit\gql\interfaces\LinkitInterface;

use craft\gql\base\ObjectType;
use craft\gql\TypeManager;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

/**
 * Class LinkitType
 */
class LinkitType extends ObjectType {
    const GRAPH_QL_FIELDS = [
        'label',
        'selectionLabel',
        'type',
        'typeHandle',
        'link',
        'url',
        'text',
        'linkAttributes',
        'target',
    ];

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo) {
        $fieldName = $resolveInfo->fieldName;

        return $source[$fieldName];
    }

    /**
     * @inheritdoc
     */
    public static function prepareLinkFieldDefinition(string $typeName): array {
        $contentFields = [];

        foreach (self::GRAPH_QL_FIELDS as $key) {
            switch ($key) {
                case 'linkAttributes':
                    $type = Type::listOf(Type::string());
                    break;
                default:
                    $type = Type::string();
            }

            $contentFields[$key] = $type;
        }

        $contentFields = TypeManager::prepareFieldDefinitions($contentFields, $typeName);

        return $contentFields;
    }
}
