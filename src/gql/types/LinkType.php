<?php

namespace presseddigital\linkit\gql\types;

use Craft;
use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\Element;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class LinkType
{
    static public function getName(): string
    {
        return 'LinkitLink';
    }

    static public function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(self::class, new ObjectType([
            'name'   => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'The interface implemented by all link types.',
        ]));
    }

    public static function getFieldDefinitions(): array
    {
        return [
            'label' => [
                'name' => 'label',
                'type' => Type::string(),
            ],
            'type' => [
                'name' => 'type',
                'type' => Type::string(),
            ],
            'typeHandle' => [
                'name' => 'typeHandle',
                'type' => Type::string(),
            ],
            'link' => [
                'name' => 'link',
                'type' => Type::string(),
            ],
            'url' => [
                'name' => 'url',
                'type' => Type::string(),
            ],
            'text' => [
                'name' => 'text',
                'type' => Type::string(),
            ],
            'target' => [
                'name' => 'target',
                'type' => Type::string(),
                'resolve' => function($source, $arguments) {
                    return $source->getTargetString();
                },
            ],
            'element' => [
                'name' => 'element',
                'type' => Element::getType(),
            ],
        ];
    }
}

