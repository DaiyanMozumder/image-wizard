<?php

namespace DaiyanMozumder\ImageWizard\Tests\Unit;

use DaiyanMozumder\ImageWizard\Facades\ImageWizard;
use DaiyanMozumder\ImageWizard\ImageWizardManager;
use DaiyanMozumder\ImageWizard\Tests\TestCase;

class FacadeTest extends TestCase
{
    public function test_facade_resolves_manager()
    {
        $manager = ImageWizard::getFacadeRoot();
        
        $this->assertInstanceOf(ImageWizardManager::class, $manager);
    }
    
    public function test_manager_receives_config()
    {
        $manager = ImageWizard::getFacadeRoot();
        $config = $manager->getConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('default_format', $config);
    }
}
