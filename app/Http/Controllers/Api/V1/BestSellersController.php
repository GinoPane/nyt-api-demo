<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\BestSellersRequest;
use App\Services\BestSellersApi\ApiClient;
use App\Services\BestSellersApi\DTO\GetBestsellersListRequest;
use Illuminate\Http\JsonResponse;

class BestSellersController
{
    public function getList(BestSellersRequest $request, ApiClient $client): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $client->getBestsellersHistory(GetBestsellersListRequest::fromRequest($request))
        ]);
    }
}
