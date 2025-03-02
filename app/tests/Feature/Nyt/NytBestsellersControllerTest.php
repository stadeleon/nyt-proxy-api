<?php

namespace Tests\Feature\Nyt;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NytBestsellersControllerTest extends TestCase
{
    #[DataProvider('bestsellersDataProvider')]
    #[Test]
    public function testBestsellersEndpointVariousScenarios(
        string $query,
        int $expectedStatus,
        array $fakeResponse,
        array $expectedJson
    ): void {
        Cache::flush();
        Http::fake([
            '*nytimes.com*' => Http::response($fakeResponse, 200),
        ]);

        $response = $this->getJson($query);

        $response->assertStatus($expectedStatus);
        $response->assertJsonFragment($expectedJson);
    }

    public static function bestsellersDataProvider(): array
    {
        return [
            'valid isbn' => [
                '/api/v1/nyt-bestsellers?isbn=9781451627282',
                200,
                [
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
                ],
                [
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
                ],
            ],
            'non-existing isbn' => [
                '/api/v1/nyt-bestsellers?isbn=DOESNOTEXIST',
                200,
                [
                    "status" => "OK",
                    "num_results" => 0,
                    "results" => [],
                ],
                [
                    "status" => "OK",
                    "num_results" => 0,
                ],
            ],
            'missing isbn' => [
                '/api/v1/nyt-bestsellers',
                200,
                [
                    "status" => "OK",
                    "num_results" => 5,
                    "results" => [
                        [
                            "title" => "Some Book",
                            "author" => "Author Name"
                        ],
                    ]
                ],
                [
                    "num_results" => 5,
                ],
            ],
            'author and title provided' => [
                '/api/v1/nyt-bestsellers?author=King&title=Dark%20Tower',
                200,
                [
                    "status" => "OK",
                    "num_results" => 2,
                    "results" => [
                        [
                            "title" => "The Dark Tower I",
                            "author" => "Stephen King",
                        ],
                        [
                            "title" => "The Dark Tower II",
                            "author" => "Stephen King",
                        ],
                    ]
                ],
                [
                    "status" => "OK",
                    "num_results" => 2,
                    "results" => [
                        [
                            "title" => "The Dark Tower I",
                            "author" => "Stephen King",
                        ],
                        [
                            "title" => "The Dark Tower II",
                            "author" => "Stephen King",
                        ],
                    ]
                ],
            ],
            'with offset' => [
                '/api/v1/nyt-bestsellers?offset=20',
                200,
                [
                    "status" => "OK",
                    "num_results" => 1,
                    "results" => [
                        [
                            "title" => "Offset Book",
                            "author" => "Offset Author",
                        ],
                    ]
                ],
                [
                    "status" => "OK",
                    "num_results" => 1,
                    "results" => [
                        [
                            "title" => "Offset Book",
                            "author" => "Offset Author",
                        ],
                    ]
                ],
            ],
        ];
    }

    #[Test]
    public function testBestsellersEndpointReturnsEmptyOnFailedResponse(): void
    {
        Http::fake([
            '*nytimes.com*' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/v1/nyt-bestsellers?isbn=FAIL');

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    #[Test]
    public function testInvalidOffsetReturnsValidationError(): void
    {
        $response = $this->getJson('/api/v1/nyt-bestsellers?offset=-1');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['offset']);
    }

    #[Test]
    public function testInvalidIsbnTooLong(): void
    {
        $isbn = str_repeat('9', 50);
        $response = $this->getJson('/api/v1/nyt-bestsellers?isbn=' . $isbn);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['isbn']);
    }
}
