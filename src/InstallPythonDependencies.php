<?php

namespace DaiyanMozumder\ImageWizard;

use Composer\Script\Event;
use Symfony\Component\Process\Process;

class InstallPythonDependencies
{
    public static function install(Event $event)
    {
        $io = $event->getIO();
        $pythonDir = dirname(__DIR__) . '/python';
        
        $io->write("<info>Checking Python Environment for Image Wizard...</info>");

        // Detect python executable
        $pythonCmd = 'python3';
        $process = new Process(['python3', '--version']);
        $process->run();
        
        if (!$process->isSuccessful()) {
            $pythonCmd = 'python';
            $process = new Process(['python', '--version']);
            $process->run();
            
            if (!$process->isSuccessful()) {
                $io->writeError("<error>Python 3 is required but could not be found on the system.</error>");
                return;
            }
        }

        $venvDir = $pythonDir . DIRECTORY_SEPARATOR . '.venv';

        // Create Virtual Environment
        if (!is_dir($venvDir)) {
            $io->write("Creating Virtual Environment in {$venvDir}...");
            $process = new Process([$pythonCmd, '-m', 'venv', $venvDir]);
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                $io->writeError("<error>Failed to create virtual environment.</error>");
                $io->writeError($process->getErrorOutput());
                return;
            }
        } else {
            $io->write("Virtual environment already exists.");
        }

        // Determine correct path for Python inside venv
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $venvPython = $isWindows 
            ? $venvDir . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe'
            : $venvDir . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';

        $io->write("Installing Python dependencies (Pillow)...");
        $process = new Process([$venvPython, '-m', 'pip', 'install', '-r', $pythonDir . DIRECTORY_SEPARATOR . 'requirements.txt']);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) use ($io) {
            $io->write($buffer, false);
        });

        if ($process->isSuccessful()) {
            $io->write("\n<info>Image Wizard Python dependencies installed successfully in isolated environment!</info>");
        } else {
            $io->writeError("\n<error>Failed to install Python dependencies.</error>");
        }
    }
}
