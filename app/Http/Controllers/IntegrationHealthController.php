<?php

namespace App\Http\Controllers;

use App\Services\IntegrationHealth;
use Illuminate\Http\JsonResponse;

class IntegrationHealthController extends Controller
{
    public function __invoke(IntegrationHealth $health): JsonResponse
    {
        return response()->json(['data' => $health->report()]);
    }
}
