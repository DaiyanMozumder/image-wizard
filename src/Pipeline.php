<?php

namespace DaiyanMozumder\ImageWizard;

class Pipeline
{
    protected string $sourcePath;
    protected array $operations = [];
    protected array $options = [];

    public function __construct(string $sourcePath)
    {
        $this->sourcePath = $sourcePath;
    }

    public function addOperation(string $type, array $params = []): self
    {
        $this->operations[] = array_merge(['type' => $type], $params);
        return $this;
    }

    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
