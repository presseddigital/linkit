<?php

namespace presseddigital\linkit\gql\types\generators;

use craft\gql\base\GeneratorInterface;

use craft\gql\base\ObjectType;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;
use presseddigital\linkit\gql\types\LinkitType;

/**
 * Class LinkitGenerator
 */
class LinkitGenerator implements GeneratorInterface, SingleGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public static function getName($context = null): string
    {
        return $context->handle . '_Linkit';
    }

    /**
     * @inheritdoc
     */
    public static function generateTypes(mixed $context = null): array
    {
        return [static::generateType($context)];
    }

    /**
     * @inheritdoc
     */
    public static function generateType(mixed $context = null): mixed
    {
        /** @var LinkitField $context */
        $typeName = self::getName($context);
        $contentFields = LinkitType::prepareLinkFieldDefinition($typeName);

        return GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new LinkitType([
            'name' => $typeName,
            'fields' => fn() => $contentFields,
        ]));
    }
}
