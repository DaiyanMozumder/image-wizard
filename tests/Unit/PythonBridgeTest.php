<?php

namespace DaiyanMozumder\ImageWizard\Tests\Unit;

use DaiyanMozumder\ImageWizard\Exceptions\PythonExecutionException;
use DaiyanMozumder\ImageWizard\PythonBridge;
use DaiyanMozumder\ImageWizard\Tests\TestCase;

class PythonBridgeTest extends TestCase
{
    protected function getPythonConfig(): array
    {
        return [
            'executable' => 'python',
            'script_path' => realpath(__DIR__ . '/../../python/engine.py'),
            'timeout' => 10,
        ];
    }

    public function test_bridge_executes_successfully()
    {
        $bridge = new PythonBridge($this->getPythonConfig());
        
        $response = $bridge->execute(['action' => 'ping']);
        
        $this->assertTrue($response['success']);
        $this->assertEquals('pong', $response['data']['message']);
    }

    public function test_bridge_throws_exception_on_python_error()
    {
        $bridge = new PythonBridge($this->getPythonConfig());
        
        $this->expectException(PythonExecutionException::class);
        $this->expectExceptionMessage('Python engine reported an error: Unknown action: invalid_action');
        
        $bridge->execute(['action' => 'invalid_action']);
    }

    public function test_bridge_throws_exception_on_invalid_input()
    {
        $bridge = new PythonBridge($this->getPythonConfig());
        
        $this->expectException(PythonExecutionException::class);
        $this->expectExceptionMessage('Python engine reported an error: No action specified');
        
        $bridge->execute(['not_an_action' => 'test']);
    }
}
