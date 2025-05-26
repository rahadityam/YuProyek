<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests; // Biasanya juga dibutuhkan
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController // Extend dari BaseController Laravel
{
    use AuthorizesRequests, ValidatesRequests; // Gunakan trait yang diperlukan
}