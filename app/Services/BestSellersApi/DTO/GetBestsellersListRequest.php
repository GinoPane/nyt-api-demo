<?php

declare(strict_types=1);

namespace App\Services\BestSellersApi\DTO;

use App\Http\Requests\BestSellersRequest;

class GetBestsellersListRequest
{
    public function __construct(
        public ?string $author,
        public ?string $title,
        /** @var string[] */
        public ?array $isbn,
        public ?int $offset
    ) {
    }

    public static function fromRequest(BestSellersRequest $request): self
    {
        $data = $request->validated();

        return new self(
            $data['author'] ?? null,
            $data['title'] ?? null,
            $data['isbn'] ?? null,
            $data['offset'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        $params = [];

        if ($this->author !== null) {
            $params['author'] = $this->author;
        }

        if ($this->title !== null) {
            $params['title'] = $this->title;
        }

        if ($this->isbn !== null) {
            $sanitizedIsbn = array_map(
                static fn (string $isbn) => preg_replace('/[^0-9]/', '', $isbn),
                $this->isbn
            );
            $params['isbn'] = implode(';', $sanitizedIsbn);
        }

        $params['offset'] = (int) $this->offset;

        return $params;
    }
}
