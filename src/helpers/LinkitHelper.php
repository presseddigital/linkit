<?php

namespace presseddigital\linkit\helpers;

class LinkitHelper
{
    // Public Methods
    // =========================================================================

    public static function getLinkHtml(string $url = '#', string $text = '', array $attributes = []): string
    {
        $attributesString = '';
        foreach ($attributes as $attribute => $value) {
            $attributesString .= ' ' . $attribute . '="' . $value . '"';
        }
        return '<a href="' . $url . '"' . $attributesString . '>' . $text . '</a>';
    }
}
