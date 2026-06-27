<?php

namespace DaiyanMozumder\ImageWizard\Tests\Unit;

use DaiyanMozumder\ImageWizard\ImageWizardManager;
use DaiyanMozumder\ImageWizard\PythonBridge;
use DaiyanMozumder\ImageWizard\Exceptions\ImageWizardException;
use PHPUnit\Framework\TestCase;

class ImageWizardManagerTest extends TestCase
{
    public function test_it_throws_exception_if_methods_called_before_load()
    {
        $manager = new ImageWizardManager([]);
        
        $this->expectException(ImageWizardException::class);
        $manager->resize(100);
    }
    
    public function test_fluent_api_builds_pipeline()
    {
        $config = ['default_format' => 'webp'];
        $manager = new ImageWizardManager($config);
        
        $manager->load('input.jpg')
                ->resize(800, 600)
                ->quality(90)
                ->watermark('logo.png', ['position' => 'bottom-right']);
                
        $pipeline = $manager->getPipeline();
        
        $this->assertNotNull($pipeline);
        $this->assertEquals('input.jpg', $pipeline->getSourcePath());
        $this->assertEquals('webp', $pipeline->getOptions()['format']);
        $this->assertEquals(90, $pipeline->getOptions()['quality']);
        
        $operations = $pipeline->getOperations();
        $this->assertCount(2, $operations);
        $this->assertEquals('resize', $operations[0]['type']);
        $this->assertEquals('watermark', $operations[1]['type']);
    }

    public function test_save_calls_bridge_and_resets_pipeline()
    {
        // Mock PythonBridge
        $bridgeMock = $this->createMock(PythonBridge::class);
        $bridgeMock->expects($this->once())
                   ->method('execute')
                   ->willReturn(['success' => true]);

        $manager = new ImageWizardManager([], $bridgeMock);
        
        $manager->load('input.jpg')->resize(100);
        $this->assertNotNull($manager->getPipeline());

        $result = $manager->save('output.jpg');
        
        $this->assertTrue($result['success']);
        $this->assertNull($manager->getPipeline()); // Pipeline should be reset
    }
}
