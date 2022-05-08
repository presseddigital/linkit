<?php

namespace presseddigital\linkit\gql\types;

use craft\gql\base\ObjectType;
use craft\gql\TypeManager;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

/**
 * Class LinkitType
 */
class LinkitType extends ObjectType
{
    public const GRAPH_QL_FIELDS = [
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
     * @param mixed[] $arguments
     */
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $fieldName = $resolveInfo->fieldName;

        return $source[$fieldName];
    }

    /**
     * @inheritdoc
     */
    public static function prepareLinkFieldDefinition(string $typeName): array
    {
        $contentFields = [];

        foreach (self::GRAPH_QL_FIELDS as $key) {
            $type = match ($key) {
                'linkAttributes' => Type::listOf(Type::string()),
                default => Type::string(),
            };

            $contentFields[$key] = $type;
        }

        $contentFields = TypeManager::prepareFieldDefinitions($contentFields, $typeName);

        return $contentFields;
    }
}
