<?php

namespace DaiyanMozumder\ImageWizard\Tests\Unit;

use DaiyanMozumder\ImageWizard\Pipeline;
use DaiyanMozumder\ImageWizard\PayloadBuilder;
use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{
    public function test_it_stores_operations_and_options()
    {
        $pipeline = new Pipeline('/tmp/source.jpg');
        $pipeline->addOperation('resize', ['width' => 100, 'height' => 100])
                 ->setOption('format', 'webp');
                 
        $this->assertCount(1, $pipeline->getOperations());
        $this->assertEquals('resize', $pipeline->getOperations()[0]['type']);
        $this->assertEquals('webp', $pipeline->getOptions()['format']);
    }

    public function test_payload_builder_formats_correctly()
    {
        $pipeline = new Pipeline('/tmp/source.jpg');
        $pipeline->addOperation('resize', ['width' => 100, 'height' => 100]);
        $pipeline->setOption('quality', 90);
        
        $builder = new PayloadBuilder();
        $payload = $builder->build($pipeline, '/tmp/dest.webp');
        
        $this->assertEquals('process', $payload['action']);
        $this->assertEquals('/tmp/source.jpg', $payload['source']);
        $this->assertEquals('/tmp/dest.webp', $payload['destination']);
        $this->assertIsArray($payload['operations']);
        $this->assertCount(1, $payload['operations']);
        $this->assertEquals(90, $payload['options']['quality']);
    }
}
