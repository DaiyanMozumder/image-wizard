<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Output Format
    |--------------------------------------------------------------------------
    */
    'default_format' => 'webp',

    /*
    |--------------------------------------------------------------------------
    | Python Engine Settings
    |--------------------------------------------------------------------------
    */
    'python' => [
        // Setting to null allows the package to automatically detect the internal .venv path.
        'executable' => env('IMAGE_WIZARD_PYTHON_EXECUTABLE', null),
        'script_path' => env('IMAGE_WIZARD_PYTHON_SCRIPT_PATH', base_path('vendor/daiyanmozumder/image-wizard/python/engine.py')),
        'timeout' => 60, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Quality Settings
    |--------------------------------------------------------------------------
    */
    'quality' => [
        'jpeg' => 80,
        'webp' => 80,
        'avif' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Variant Presets
    |--------------------------------------------------------------------------
    */
    'variants' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'fit' => 'crop'],
        'small'     => ['width' => 300, 'height' => 300, 'fit' => 'contain'],
        'medium'    => ['width' => 800, 'height' => 800, 'fit' => 'contain'],
        'large'     => ['width' => 1200, 'height' => 1200, 'fit' => 'contain'],
        'xlarge'    => ['width' => 1920, 'height' => 1080, 'fit' => 'contain'],
    ],
];
