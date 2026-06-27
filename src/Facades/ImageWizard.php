<?php

namespace DaiyanMozumder\ImageWizard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \DaiyanMozumder\ImageWizard\ImageWizardManager load(string $path)
 *
 * @see \DaiyanMozumder\ImageWizard\ImageWizardManager
 */
class ImageWizard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'image-wizard';
    }
}
