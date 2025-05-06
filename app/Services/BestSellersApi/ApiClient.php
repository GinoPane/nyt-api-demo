<?php

declare(strict_types=1);

namespace App\Services\BestSellersApi;

use App\Services\BestSellersApi\DTO\GetBestsellersListRequest;
use App\Services\BestSellersApi\DTO\GetBestsellersListResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApiClient
{
    public function getBestsellersHistory(GetBestsellersListRequest $data): GetBestsellersListResponse
    {
        $key = 'nyt_history_' . md5(json_encode($data->toQuery()));

        $response = Cache::remember($key, now()->addWeek(), function () use ($data) {
            $httpResponse = Http::get(
                config('app.nyt_api.base_url') . config('app.nyt_api.bestsellers_history'),
                $data->toQuery() + ['api-key' => config('app.nyt_api.key')]
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

        return new GetBestsellersListResponse($response['num_results'] ?? 0, $response['results'] ?? []);
    }
}
