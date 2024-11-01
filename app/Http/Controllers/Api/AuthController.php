<?php

namespace App\Http\Controllers\Api;

use App\Domain\UseCases\LoginUser\LoginUserInputPort;
use App\Domain\UseCases\LoginUser\LoginUserRequestModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginUserInputPort $loginUserInputPort
    )
    {
    }

    public function login(Request $request): JsonResponse
    {
        $response = $this->loginUserInputPort->handle(new LoginUserRequestModel($request->all()));
        dd($response);
        return response()->json();
    }
}
