<?php

namespace App\Http\Controllers\AreaRestrita;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class PainelController extends Controller
{
    public function index(): View
    {
        return view('area-restrita.painel');
    }
}
