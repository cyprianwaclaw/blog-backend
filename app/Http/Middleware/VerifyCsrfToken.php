<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    //musi byc, " '/api/*'" bo przy polaczeniu ze strona pojawia sie blad tokenu crf
    protected $except = [
        '/api/*'
    ];

}
