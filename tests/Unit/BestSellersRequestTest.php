<?php

namespace Tests\Unit;

use App\Http\Requests\BestSellersRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BestSellersRequestTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function test_bestsellers_request_validation(array $queryParams, bool $shouldPass): void
    {
        $request = new BestSellersRequest();

        $validator = Validator::make($queryParams, $request->rules());

        $this->assertSame($shouldPass, $validator->passes(), print_r($validator->errors(), true));
    }

    public static function dataProvider(): array
    {
        return [
            'valid isbn-10, offset 20' => [
                [
                    'isbn' => ['0306406152'],
                    'offset' => 20,
                    'title' => 'Valid Book',
                    'author' => 'Author Name'
                ],
                true,
            ],
            'valid isbn-13, offset 40' => [
                [
                    'isbn' => ['9783161484100'],
                    'offset' => 40,
                ],
                true,
            ],
            'multiple valid isbns' => [
                [
                    'isbn' => ['0306406152', '9783161484100'],
                    'offset' => 60,
                ],
                true,
            ],
            'invalid isbn format' => [
                [
                    'isbn' => ['INVALIDISBN'],
                    'offset' => 20,
                ],
                false,
            ],
            'invalid isbn checksum' => [
                [
                    'isbn' => ['9783161484101'], // invalid check digit
                    'offset' => 20,
                ],
                false,
            ],
            'offset not multiple of 20' => [
                [
                    'isbn' => ['9783161484100'],
                    'offset' => 25,
                ],
                false,
            ],
            'missing offset (should default to 20)' => [
                [
                    'isbn' => ['0306406152'],
                ],
                true,
            ],
            'empty input (everything optional)' => [
                [],
                true,
            ],
            'isbn array with empty string' => [
                [
                    'isbn' => [''],
                    'offset' => 20,
                ],
                false,
            ],
        ];
    }
}
