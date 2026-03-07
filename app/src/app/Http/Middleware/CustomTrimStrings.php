<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as BaseTrimmer;

class CustomTrimStrings extends BaseTrimmer
{
    /**
     * The URIs that should be excluded from trimming.
     *
     * @var array
     */
    protected $except = [
        'code',
        'expected_output',
    ];
}
