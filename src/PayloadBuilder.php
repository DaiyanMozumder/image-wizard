<?php

namespace DaiyanMozumder\ImageWizard;

class PayloadBuilder
{
    public function build(Pipeline $pipeline, string $destinationPath): array
    {
        return [
            'action' => 'process',
            'source' => $pipeline->getSourcePath(),
            'destination' => $destinationPath,
            'operations' => $pipeline->getOperations(),
            'options' => $pipeline->getOptions(),
        ];
    }
}
