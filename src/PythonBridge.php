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
        $this->executable = $config['executable'] ?? 'python';
        // Fallback to finding the python folder relative to src directory
        $this->scriptPath = $config['script_path'] ?? dirname(__DIR__) . '/python/engine.py';
        $this->timeout = $config['timeout'] ?? 60;
    }

    /**
     * Execute the Python engine with the given payload.
     *
     * @param array $payload
     * @return array
     * @throws PythonExecutionException
     */
    public function execute(array $payload): array
    {
        $process = $this->createProcess();
        $process->setInput(json_encode($payload));

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
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

    /**
     * Create the Symfony Process instance.
     * Protected to allow overriding in tests if needed.
     *
     * @return Process
     */
    protected function createProcess(): Process
    {
        $process = new Process([$this->executable, $this->scriptPath]);
        $process->setTimeout($this->timeout);
        return $process;
    }
}
