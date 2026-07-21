<?php

namespace DaiyanMozumder\ImageWizard;

use DaiyanMozumder\ImageWizard\Exceptions\PythonExecutionException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PythonBridge
{
    protected string $executable;
    protected string $scriptPath;
    protected int $timeout;

    public function __construct(array $config)
    {
        $this->executable = $config['executable'] ?? $this->detectVirtualEnvPython();
        $this->scriptPath = $config['script_path'] ?? dirname(__DIR__) . '/python/engine.py';
        $this->timeout = $config['timeout'] ?? 60;
    }

    public function execute(array $payload): array
    {
        $process = $this->createProcess();
        $process->setInput(json_encode($payload));

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $output = $process->getOutput();
            $result = json_decode($output, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($result['success']) && $result['success'] === false) {
                $error = $result['error'] ?? 'Unknown Python error';
                throw new PythonExecutionException("Python engine reported an error: " . $error, 0, $e);
            }
            throw new PythonExecutionException("Python execution failed: " . $e->getMessage(), 0, $e);
        }

        $output = $process->getOutput();
        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PythonExecutionException("Failed to parse Python engine output. Output was: " . $output);
        }

        if (isset($result['success']) && $result['success'] === false) {
            $error = $result['error'] ?? 'Unknown Python error';
            throw new PythonExecutionException("Python engine reported an error: " . $error);
        }

        return $result;
    }

    protected function createProcess(): Process
    {
        $process = new Process([$this->executable, $this->scriptPath]);
        $process->setTimeout($this->timeout);
        return $process;
    }
    
    /**
     * Detect the internal isolated virtual environment python executable.
     */
    protected function detectVirtualEnvPython(): string
    {
        $basePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . '.venv';
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        $venvPython = $isWindows 
            ? $basePath . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe'
            : $basePath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';
            
        if (file_exists($venvPython)) {
            return $venvPython;
        }
        
        // Fallback to system python if venv is missing
        return 'python';
    }
}
