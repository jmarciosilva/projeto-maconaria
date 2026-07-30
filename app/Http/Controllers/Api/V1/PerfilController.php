<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UsuarioResource;
use Illuminate\Http\Request;

final class PerfilController extends Controller
{
    public function show(Request $request): UsuarioResource
    {
        $usuario = $request->user()->load('irmao');

        return new UsuarioResource($usuario);
    }
}
