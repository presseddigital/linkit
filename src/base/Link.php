<?php
namespace fruitstudios\linkit\base;

use Craft;
use craft\base\SavableComponent;

use fruitstudios\linkit\helpers\LinkItHelper;

abstract class Link extends SavableComponent implements LinkTypeInterface
{
    // Public
    // =========================================================================

    public $type;

    public $value = '';
    public $text = '';
    public $target = false;

    // Public Methods
    // =========================================================================

	public function __toString(): string
    {
        return (string) $this->getLink();
    }

    public function getLink(): string
    {
    	return LinkItHelper::getLinkHtml($this->getUrl(), $this->text, $this->getLinkAttributes());
    }

    public function getUrl(): string
    {
		return (string) $this->value;
    }

    public function getLinkAttributes(): array
    {
    	$attributes = [];
    	if($this->target)
    	{
    		$attributes['target'] = '_blank';
    	}
    }

}
