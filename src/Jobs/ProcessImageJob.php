<?php

namespace DaiyanMozumder\ImageWizard\Jobs;

use DaiyanMozumder\ImageWizard\PythonBridge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(PythonBridge $bridge): void
    {
        try {
            $result = $bridge->execute($this->payload);
            
            if (!$result['success']) {
                Log::error("Image Wizard background process failed.", ['error' => $result['error'] ?? 'Unknown']);
                $this->fail(new \Exception($result['error'] ?? 'Unknown error in Python Bridge'));
            }
        } catch (\Exception $e) {
            Log::error("Image Wizard job threw an exception.", ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
}
