<?php

namespace Tests\Unit\Nyt;

use App\Services\Nyt\NytApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class NytApiServiceTest extends TestCase
{
    #[Test]
    public function testGetBestsellersReturnsJsonOnSuccess(): void
    {
        config()->set('services.nyt.api_key', 'TEST_API_KEY');

        $params = ['isbn' => '9781451627282'];

        $expectedData = [
            "status" => "OK",
            "num_results" => 1,
            "results" => [
                [
                    "title" => "11/22/63",
                    "author" => "Stephen King",
                    "description" => "An English teacher travels back to 1958...",
                    "contributor" => "by Stephen King",
                    "publisher" => "Pocket Books",
                ]
            ]
        ];

        Http::fake([
            '*nytimes.com/*' => Http::response($expectedData, 200),
        ]);

        $service = new NytApiService();
        $result = $service->getBestsellers($params);

        $this->assertEquals($expectedData, $result);
    }

    #[Test]
    public function testGetBestsellersReturnsEmptyOnFailure(): void
    {
        config()->set('services.nyt.api_key', 'TEST_API_KEY');

        $params = ['isbn' => '9781451627282'];

        Http::fake([
            '*nytimes.com/*' => Http::response([], 500),
        ]);

        $service = new NytApiService();
        $result = $service->getBestsellers($params);

        $this->assertEmpty($result);
    }
}
