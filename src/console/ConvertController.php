<?php

namespace presseddigital\linkit\console;

use Craft;
use craft\console\Controller;

class ConvertController extends Controller
{
    public $defaultAction = 'convert';

    public function actionIndex(): int
    {
        return 1;
    }

    public function actionConvert(): int
    {
        return 1;
    }

}