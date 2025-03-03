<?php

namespace Tests\Feature\Http\Requests\Nyt;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Nyt\NytBestsellersRequest;

class NytBestsellersRequestTest extends TestCase
{
    public function test_valid_data_passes_validation(): void
    {
        $data = [
            'author' => 'John Doe',
            'title'  => 'Great Book',
            'isbn[]'   => '1234567890123',
            'offset' => 10,
        ];

        $validator = Validator::make($data, (new NytBestsellersRequest())->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_offset_fails_validation(): void
    {
        $data = ['offset' => -5];

        $validator = Validator::make($data, (new NytBestsellersRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('offset', $validator->errors()->toArray());
    }

    public function test_invalid_isbn_length_fails_validation(): void
    {
        $data = ['isbn' => str_repeat('1', 21)];

        $validator = Validator::make($data, (new NytBestsellersRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('isbn', $validator->errors()->toArray());
    }

    public function test_nullable_fields_are_optional(): void
    {
        $data = [];

        $validator = Validator::make($data, (new NytBestsellersRequest())->rules());

        $this->assertTrue($validator->passes());
    }
}
