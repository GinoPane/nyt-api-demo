<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
                'success' => true,
                'data' => [
                    'user' => $request->user()->only(['id', 'name', 'email'])
                ]
            ]);
    }
}
