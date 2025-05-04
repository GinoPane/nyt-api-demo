<?php

declare(strict_types=1);

namespace App\Services\BestSellersApi\DTO;

class GetListResponse
{
    public function __construct(
        public int $count,
        /** @var mixed[] */
        public array $results,
    ) {
    }
}
