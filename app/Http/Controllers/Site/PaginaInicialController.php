<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class PaginaInicialController extends Controller
{
    public function index(): View
    {
        return view('site.home');
    }
}
