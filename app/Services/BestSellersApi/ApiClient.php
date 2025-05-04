<?php

declare(strict_types=1);

namespace App\Services\BestSellersApi;

use App\Services\BestSellersApi\DTO\GetListRequest;
use App\Services\BestSellersApi\DTO\GetListResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApiClient
{
    public function getList(GetListRequest $data): GetListResponse
    {
        $key = 'nyt_history_' . md5(json_encode($data->toQueryFormat()));

        $response = Cache::remember($key, now()->addWeek(), function () use ($data) {
            $httpResponse = Http::get(
                config('app.nyt_api.base_url') . 'books/v3/lists/best-sellers/history.json',
                $data->toQueryFormat()
            );

            if ($httpResponse->ok()) {
                return $httpResponse->json();
            }

            $responseData = $httpResponse->json();

            throw new RuntimeException(
                'Error fetching bestsellers: ' .
                ($responseData['errors'][0] ?? ($responseData['fault']['faultstring'] ?? 'Unknown error'))
            );
        });

        return new GetListResponse($response['num_results'] ?? 0, $response['results'] ?? []);
    }
}
