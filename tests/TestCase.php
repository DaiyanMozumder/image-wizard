<?php

namespace DaiyanMozumder\ImageWizard\Tests;

use DaiyanMozumder\ImageWizard\ImageWizardServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ImageWizardServiceProvider::class,
        ];
    }
}
