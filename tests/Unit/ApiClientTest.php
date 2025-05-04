<?php

namespace Tests\Unit\Services\BestSellersApi;

use App\Services\BestSellersApi\ApiClient;
use App\Services\BestSellersApi\DTO\GetListRequest;
use App\Services\BestSellersApi\DTO\GetListResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ApiClientTest extends TestCase
{
    protected ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = new ApiClient();
    }

    public function testGetListReturnsData(): void
    {
        $request = new GetListRequest(author: 'Diana Gabaldon', title: 'I GIVE YOU MY BODY ...', isbn: ['0399178570'], offset: 0);

        $mockedResponse = json_decode('{
  "status": "OK",
  "copyright": "Copyright (c) 2025 The New York Times Company.  All Rights Reserved.",
  "num_results": 1,
  "results": [
    {
      "title": "\"I GIVE YOU MY BODY ...\"",
      "description": "The author of the Outlander novels gives tips on writing sex scenes, drawing on examples from the books.",
      "contributor": "by Diana Gabaldon",
      "author": "Diana Gabaldon",
      "contributor_note": "",
      "price": "0.00",
      "age_group": "",
      "publisher": "Dell",
      "isbns": [
        {
          "isbn10": "0399178570",
          "isbn13": "9780399178573"
        }
      ],
      "ranks_history": [
        {
          "primary_isbn10": "0399178570",
          "primary_isbn13": "9780399178573",
          "rank": 8,
          "list_name": "Advice How-To and Miscellaneous",
          "display_name": "Advice, How-To & Miscellaneous",
          "published_date": "2016-09-04",
          "bestsellers_date": "2016-08-20",
          "weeks_on_list": 1,
          "rank_last_week": 0,
          "asterisk": 0,
          "dagger": 0
        }
      ],
      "reviews": [
        {
          "book_review_link": "",
          "first_chapter_link": "",
          "sunday_review_link": "",
          "article_chapter_link": ""
        }
      ]
    }
  ]
}', true);

        Http::fake([
            '*' => Http::response($mockedResponse, 200),
        ]);

        $response = $this->apiClient->getList($request);

        $this->assertInstanceOf(GetListResponse::class, $response);
        $this->assertEquals(1, $response->count);
        $this->assertSame('"I GIVE YOU MY BODY ..."', $response->results[0]['title']);
        $this->assertSame('Diana Gabaldon', $response->results[0]['author']);
    }

    public function testGetListThrowsExceptionForHttpError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error fetching bestsellers: Offset not a multiple of paging size 20');

        $request = new GetListRequest(author: 'Jane Doe', title: 'Another Title', isbn: null, offset: -1);

        $mockedResponse = json_decode('{
  "status": "ERROR",
  "copyright": "Copyright (c) 2025 The New York Times Company.  All Rights Reserved.",
  "errors": [
    "Offset not a multiple of paging size 20",
    "Bad Request"
  ],
  "results": []
}', true);

        Http::fake([
            '*' => Http::response($mockedResponse, 400),
        ]);

        $this->apiClient->getList($request);
    }

    public function testGetListHandlesEmptyResults(): void
    {
        $request = new GetListRequest(author: null, title: null, isbn: null, offset: 0);

        $mockedResponse = [
            'num_results' => 0,
            'results' => [],
        ];

        Http::fake([
            '*' => Http::response($mockedResponse, 200),
        ]);

        $response = $this->apiClient->getList($request);

        $this->assertInstanceOf(GetListResponse::class, $response);
        $this->assertEquals(0, $response->count);
        $this->assertEquals([], $response->results);
    }

    public function testGetListHandlesUnknownError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error fetching bestsellers: Unknown error');

        $request = new GetListRequest(author: 'Unknown', title: null, isbn: null, offset: 10);

        $mockedResponse = [];

        Http::fake([
            '*' => Http::response($mockedResponse, 500),
        ]);

        $this->apiClient->getList($request);
    }
}
