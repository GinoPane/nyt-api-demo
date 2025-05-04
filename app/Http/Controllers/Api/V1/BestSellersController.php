<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\BestSellersRequest;
use App\Services\BestSellersApi\ApiClient;
use App\Services\BestSellersApi\DTO\GetListRequest;
use Illuminate\Http\JsonResponse;

class BestSellersController
{
    public function getList(BestSellersRequest $request, ApiClient $client): JsonResponse
    {
        return response()->json($client->getList(GetListRequest::fromRequest($request)));
    }
}
