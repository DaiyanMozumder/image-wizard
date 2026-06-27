<p align="center">
    <img src="https://raw.githubusercontent.com/daiyanmozumder/image-wizard/main/art/logo.png" alt="Image Wizard" width="400">
</p>

<p align="center">
    <strong>Enterprise Laravel Image Processing powered by Python</strong>
</p>

<p align="center">
    <a href="https://packagist.org/packages/daiyanmozumder/image-wizard"><img src="https://img.shields.io/packagist/v/daiyanmozumder/image-wizard.svg?style=flat-square" alt="Latest Version on Packagist"></a>
    <a href="https://packagist.org/packages/daiyanmozumder/image-wizard"><img src="https://img.shields.io/packagist/dt/daiyanmozumder/image-wizard.svg?style=flat-square" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/daiyanmozumder/image-wizard"><img src="https://img.shields.io/packagist/php-v/daiyanmozumder/image-wizard.svg?style=flat-square" alt="PHP Version Require"></a>
    <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-success.svg?style=flat-square" alt="License"></a>
</p>

---

**Image Wizard** is an enterprise-grade Laravel image processing package. Instead of relying on PHP's memory-hungry GD/Imagick libraries, it bridges Laravel to a high-performance Python engine powered by the `Pillow` library.

Laravel handles the orchestration, fluent API, and background queueing, while Python executes the heavy CPU-bound image manipulation. 

## ✨ Features

- **Blazing Fast**: Image processing runs in an isolated Python process. No more `Allowed memory size exhausted` errors in PHP!
- **Fluent API**: Clean, intuitive, and chainable syntax (`->resize()->watermark()->save()`).
- **Laravel Queues**: Send massive batch jobs to the background instantly with `->queue()`.
- **Cloud Storage (S3)**: Seamlessly pull/push to AWS S3 or Local disks with `->fromDisk()` and `->toDisk()`.
- **Variant Generator**: Automatically spawn `thumbnail`, `medium`, and `large` responsive variants based on config presets.
- **Watermark Engine**: Apply image watermarks with precise CSS-like positioning and alpha opacity.
- **Next-Gen Formats**: Built-in support for converting to WebP and AVIF.

---

## 📦 Requirements

- PHP 8.1+
- Laravel 10.0, 11.0, or 12.0+
- Python 3+
- Pillow (`pip install Pillow>=10.0.0`)

---

## 🚀 Installation

1. Require the package via Composer:
```bash
composer require daiyanmozumder/image-wizard
```

2. Publish the configuration file:
```bash
php artisan vendor:publish --tag=image-wizard-config
```

3. Ensure you have Python and the Pillow library installed on your server/environment:
```bash
pip install Pillow
```

---

## ⚙️ Configuration

Open `config/image-wizard.php`. Here you can define default formats, compression quality, queue settings, and responsive variant presets.

```php
return [
    'default_format' => 'webp', // Convert everything to webp by default

    'python' => [
        'executable' => env('IMAGE_WIZARD_PYTHON_EXECUTABLE', 'python3'),
        'timeout' => 60, // Maximum execution time in seconds
    ],

    'quality' => [
        'jpeg' => 80,
        'webp' => 80,
        'avif' => 50,
    ],

    'variants' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'fit' => 'crop'],
        'medium'    => ['width' => 800, 'height' => 800, 'fit' => 'contain'],
        'large'     => ['width' => 1200, 'height' => 1200, 'fit' => 'contain'],
    ],
];
```

---

## 📖 Usage Guide

### Basic Processing

Chain commands together effortlessly. Execution is delayed until you call `save()`, allowing for clean architecture.

```php
use ImageWizard;

ImageWizard::load('public/uploads/raw.jpg')
    ->resize(800, 600, 'cover')
    ->format('webp')
    ->quality(85)
    ->save('public/images/optimized.webp');
```

### Advanced Resizing (Fit Strategies)

Pass a fit strategy as the third argument to `resize()` to control how the image scales.

```php
ImageWizard::load('image.jpg')
    ->resize(500, 500, 'contain') // (Default) Scale to fit within bounds
    // ->resize(500, 500, 'cover')   // Scale to fill bounds exactly, cropping overflow
    // ->resize(500, 500, 'stretch') // Ignore aspect ratio, force to exact dimensions
    // ->resize(500, 500, 'pad')     // Fit within bounds, adding transparent/white padding
    ->save('output.jpg');
```

### Watermarks

Overlay logos onto your images. You can control opacity, margin, and CSS-like positioning (`bottom-right`, `top-left`, `center`, etc.).

```php
ImageWizard::load('photo.jpg')
    ->watermark('logo.png', [
        'position' => 'bottom-right',
        'opacity' => 0.6, // 60% opacity
        'margin' => 20    // 20px offset from the edge
    ])
    ->save('photo-watermarked.jpg');
```

### Cloud Storage (AWS S3)

Image Wizard integrates natively with Laravel's `Storage` facade. It automatically streams files from S3 to a local temp folder, processes them via Python, streams them back to S3, and cleans up the temp files.

```php
ImageWizard::fromDisk('s3', 'raw-uploads/user.jpg')
    ->resize(1200)
    ->watermark('watermark.png')
    ->toDisk('s3', 'optimized/user.jpg');
```

### Background Processing (Queues)

Processing large 4K images blocks the PHP thread. Use `queue()` to dispatch a background job instantly instead of `save()`.

```php
// Instantly returns a response to the user
ImageWizard::load('massive-raw-file.tiff')
    ->format('avif')
    ->resize(2000)
    ->queue('public/optimized.avif');
```

### Automatic Responsive Variants

Instead of manually creating multiple sizes, use your config presets to generate them all at once.

```php
ImageWizard::load('hero.png')
    ->generateVariants('public/hero.jpg', ['thumbnail', 'medium', 'large']);
```
This generates:
- `public/hero-thumbnail.jpg`
- `public/hero-medium.jpg`
- `public/hero-large.jpg`

### Batch Processing

Apply a strict pipeline to an entire array of files.

```php
$files = ['img1.jpg', 'img2.png', 'img3.webp'];

ImageWizard::resize(500, 500, 'crop')
    ->format('webp')
    ->batch($files, 'public/batch-output/');
```

### Preserving Metadata (EXIF)

By default, EXIF data (GPS, camera info) is stripped to optimize file size. If you are building a photography portfolio, preserve it easily:

```php
ImageWizard::load('photo.jpg')
    ->preserveMetadata()
    ->save('photo-preserved.jpg');
```

---

## 🛠️ Troubleshooting

- **Python execution failed / File not found**: 
  Ensure `python` or `python3` is available in your server's `$PATH`. If using Docker or a specific environment, update the `executable` path in `config/image-wizard.php`.
  
- **JSON Decode Error**: 
  The PHP bridge expects strict JSON from Python. If you have modified the Python environment to print debug warnings to `stdout`, it will break the bridge. Ensure your Python script runs silently.

- **Missing Pillow**: 
  If you get an internal engine error mentioning `PIL`, run `pip install Pillow` on your host machine.

---

## 🛡️ Security

If you discover any security related issues, please email `hello@example.com` instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
