<?php

namespace App\Http\Controllers;

use App\Services\AiGateway;
use App\Services\IntegrationHealth;
use Illuminate\Http\JsonResponse;

class IntegrationHealthController extends Controller
{
    public function __invoke(IntegrationHealth $health, AiGateway $ai): JsonResponse
    {
        return response()->json(['data' => $health->report($ai)]);
    }
}
