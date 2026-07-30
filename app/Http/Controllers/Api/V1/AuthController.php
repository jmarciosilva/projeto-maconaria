<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginApiRequest;
use App\Http\Resources\Api\V1\UsuarioResource;
use App\Support\RegistradorDeAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function login(LoginApiRequest $request): JsonResponse
    {
        $usuario = $request->autenticar();

        $token = $usuario->createToken($request->string('device_name')->value())->plainTextToken;

        RegistradorDeAuditoria::registrar('login-api', 'autenticacao', 'User', $usuario->id);

        return response()->json([
            'token' => $token,
            'usuario' => new UsuarioResource($usuario),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // currentAccessToken() só existe quando a requisição foi autenticada
        // via Sanctum (garantido pelo middleware auth:sanctum na rota).
        $request->user()->currentAccessToken()->delete();

        RegistradorDeAuditoria::registrar('logout-api', 'autenticacao', 'User', $request->user()->id);

        return response()->json(['mensagem' => 'Sessão encerrada com sucesso.']);
    }
}
