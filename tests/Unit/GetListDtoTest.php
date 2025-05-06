<?php

namespace Tests\Unit;

use App\Http\Requests\BestSellersRequest;
use App\Services\BestSellersApi\DTO\GetBestsellersListRequest;
use Mockery;
use Tests\TestCase;

class GetListDtoTest extends TestCase
{
    public function test_from_request_with_valid_data(): void
    {
        $requestMock = Mockery::mock(BestSellersRequest::class);
        $requestMock->shouldReceive('validated')->andReturn([
            'author' => 'John Doe',
            'title' => 'Sample Title',
            'isbn' => ['1234567890', '0987654321'],
            'offset' => 20,
        ]);

        $getList = GetBestsellersListRequest::fromRequest($requestMock);

        $this->assertInstanceOf(GetBestsellersListRequest::class, $getList);
        $this->assertSame('John Doe', $getList->author);
        $this->assertSame('Sample Title', $getList->title);
        $this->assertSame(['1234567890', '0987654321'], $getList->isbn);
        $this->assertSame(20, $getList->offset);
    }

    public function test_resulting_query_from_request_with_valid_data(): void
    {
        $requestMock = Mockery::mock(BestSellersRequest::class);
        $requestMock->shouldReceive('validated')->andReturn([
            'author' => 'John Doe',
            'title' => 'Sample Title',
            'isbn' => ['1234567890', '0987654321'],
            'offset' => 20,
        ]);

        $expected = [
            'author' => 'John Doe',
            'title' => 'Sample Title',
            'isbn' => '1234567890;0987654321',
            'offset' => 20,
        ];

        $getList = GetBestsellersListRequest::fromRequest($requestMock);

        $this->assertInstanceOf(GetBestsellersListRequest::class, $getList);
        $this->assertSame($expected, $getList->toQuery());
    }

    public function test_from_request_with_partial_data(): void
    {
        $requestMock = Mockery::mock(BestSellersRequest::class);
        $requestMock->shouldReceive('validated')->andReturn([
            'author' => 'Jane Smith',
            'offset' => 20,
        ]);

        $getList = GetBestsellersListRequest::fromRequest($requestMock);

        $this->assertInstanceOf(GetBestsellersListRequest::class, $getList);
        $this->assertSame('Jane Smith', $getList->author);
        $this->assertNull($getList->title);
        $this->assertNull($getList->isbn);
        $this->assertSame(20, $getList->offset);
    }

    public function test_from_request_with_no_data(): void
    {
        $requestMock = Mockery::mock(BestSellersRequest::class);
        $requestMock->shouldReceive('validated')->andReturn([]);

        $getList = GetBestsellersListRequest::fromRequest($requestMock);

        $this->assertInstanceOf(GetBestsellersListRequest::class, $getList);
        $this->assertNull($getList->author);
        $this->assertNull($getList->title);
        $this->assertNull($getList->isbn);
        $this->assertNull($getList->offset);
    }
}
