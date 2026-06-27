<?php

namespace DaiyanMozumder\ImageWizard;

use DaiyanMozumder\ImageWizard\Exceptions\ImageWizardException;
use DaiyanMozumder\ImageWizard\Jobs\ProcessImageJob;

class ImageWizardManager
{
    protected array $config;
    protected ?Pipeline $pipeline = null;
    protected PythonBridge $bridge;
    protected ValidationLayer $validator;
    protected StorageManager $storage;
    protected ?string $tempSourcePath = null;

    public function __construct(array $config, ?PythonBridge $bridge = null, ?ValidationLayer $validator = null, ?StorageManager $storage = null)
    {
        $this->config = $config;
        $this->bridge = $bridge ?? new PythonBridge($config['python'] ?? []);
        $this->validator = $validator ?? new ValidationLayer();
        $this->storage = $storage ?? new StorageManager();
    }

    public function load(string $path): self
    {
        $this->validator->validateSource($path);
        
        $this->pipeline = new Pipeline($path);
        
        if (isset($this->config['default_format'])) {
            $this->format($this->config['default_format']);
        }
        
        return $this;
    }

    public function fromDisk(string $disk, string $path): self
    {
        $this->tempSourcePath = $this->storage->downloadToTemp($disk, $path);
        return $this->load($this->tempSourcePath);
    }

    public function resize(int $width, ?int $height = null, string $fit = 'contain'): self
    {
        $this->ensurePipeline();
        $this->pipeline->addOperation('resize', [
            'width' => $width,
            'height' => $height,
            'fit' => $fit
        ]);
        return $this;
    }
    
    public function format(string $format): self
    {
        $this->ensurePipeline();
        $this->pipeline->setOption('format', $format);
        return $this;
    }

    public function quality(int $quality): self
    {
        $this->ensurePipeline();
        $this->pipeline->setOption('quality', $quality);
        return $this;
    }

    public function preserveMetadata(bool $preserve = true): self
    {
        $this->ensurePipeline();
        $this->pipeline->setOption('preserveMetadata', $preserve);
        return $this;
    }

    public function watermark(string $path, array $options = []): self
    {
        $this->ensurePipeline();
        
        if (!file_exists($path)) {
            throw new ImageWizardException("Watermark image not found: {$path}");
        }
        
        $this->pipeline->addOperation('watermark', array_merge(['path' => $path], $options));
        return $this;
    }

    public function save(string $destinationPath): array
    {
        $this->ensurePipeline();
        
        $builder = new PayloadBuilder();
        $payload = $builder->build($this->pipeline, $destinationPath);
        
        $result = $this->bridge->execute($payload);
        
        $this->pipeline = null;
        
        if ($this->tempSourcePath) {
            $this->storage->cleanup($this->tempSourcePath);
            $this->tempSourcePath = null;
        }
        
        return $result;
    }

    public function toDisk(string $disk, string $destinationPath): array
    {
        $this->ensurePipeline();
        
        $format = $this->pipeline->getOptions()['format'] ?? pathinfo($destinationPath, PATHINFO_EXTENSION);
        $tempDestPath = $this->storage->generateTempPath($format);
        
        $result = $this->save($tempDestPath);
        
        if ($result['success']) {
            $this->storage->uploadFromTemp($tempDestPath, $disk, $destinationPath);
        }
        
        $this->storage->cleanup($tempDestPath);
        
        if (isset($result['data']['destination'])) {
            $result['data']['destination'] = $destinationPath;
            $result['data']['disk'] = $disk;
        }
        
        return $result;
    }

    public function queue(string $destinationPath): bool
    {
        $this->ensurePipeline();
        
        $builder = new PayloadBuilder();
        $payload = $builder->build($this->pipeline, $destinationPath);
        
        $this->pipeline = null;
        
        ProcessImageJob::dispatch($payload);
        
        return true;
    }

    public function batch(array $sources, string $destinationDir, callable $callback = null): array
    {
        $results = [];
        $operations = $this->pipeline ? $this->pipeline->getOperations() : [];
        $options = $this->pipeline ? $this->pipeline->getOptions() : [];
        
        foreach ($sources as $source) {
            $this->load($source);
            foreach ($operations as $op) {
                $this->pipeline->addOperation($op['type'], $op);
            }
            foreach ($options as $key => $val) {
                $this->pipeline->setOption($key, $val);
            }
            
            $filename = basename($source);
            $destPath = rtrim($destinationDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
            
            if ($callback) {
                $destPath = $callback($source, $destPath) ?: $destPath;
            }
            
            $results[$source] = $this->save($destPath);
        }
        
        return $results;
    }

    public function generateVariants(string $destinationPath, array $variants = []): array
    {
        $this->ensurePipeline();
        
        $presets = $this->config['variants'] ?? [];
        if (empty($variants)) {
            $variants = array_keys($presets);
        }
        
        $sourcePath = $this->pipeline->getSourcePath();
        $results = [];
        
        $pathInfo = pathinfo($destinationPath);
        $dir = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'] . DIRECTORY_SEPARATOR;
        $filename = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        foreach ($variants as $variantName) {
            if (!isset($presets[$variantName])) {
                continue;
            }
            
            $preset = $presets[$variantName];
            
            $this->load($sourcePath);
            
            if (isset($preset['width']) || isset($preset['height'])) {
                $this->resize(
                    $preset['width'] ?? null, 
                    $preset['height'] ?? null, 
                    $preset['fit'] ?? 'contain'
                );
            }
            
            if (isset($preset['format'])) {
                $this->format($preset['format']);
            }
            
            if (isset($preset['quality'])) {
                $this->quality($preset['quality']);
            }

            $variantDest = $dir . $filename . '-' . $variantName . $extension;
            $results[$variantName] = $this->save($variantDest);
        }
        
        return $results;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
    
    public function getPipeline(): ?Pipeline
    {
        return $this->pipeline;
    }

    protected function ensurePipeline(): void
    {
        if (!$this->pipeline) {
            throw new ImageWizardException("No image loaded. Call load() first.");
        }
    }
}
