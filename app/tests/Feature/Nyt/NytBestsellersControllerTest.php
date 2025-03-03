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
        array $expectedJson,
        array $fakeResponse = null
    ): void {
        Cache::flush();
        if ($fakeResponse) {
            Http::fake([
                '*nytimes.com*' => Http::response($fakeResponse, 200),
            ]);
        } else {
            $this->getFakerForMultipleISBNs();
        }

        $response = $this->getJson($query);

        $response->assertStatus($expectedStatus);
        $response->assertJsonFragment($expectedJson);
    }

    private function getFakerForMultipleISBNs() {
        return Http::fake(function ($request) {
            $query = $request->url();

            if (str_contains($query, 'isbn=9781451627282')) {
                return Http::response([
                    "status" => "OK",
                    "num_results" => 1,
                    "results" => [
                        [
                            "title" => "11/22/63",
                            "author" => "Stephen King",
                        ],
                    ]
                ], 200);
            }

            if (str_contains($query, 'isbn=9780399169274')) {
                return Http::response([
                    "status" => "OK",
                    "num_results" => 1,
                    "results" => [
                        [
                            "title" => "#GIRLBOSS",
                            "author" => "Sophia Amoruso",
                        ],
                    ]
                ], 200);
            }

            return Http::response(["status" => "OK", "num_results" => 0, "results" => []], 200);
        });
    }

    public static function bestsellersDataProvider(): array
    {
        return [
            'multiple valid isbns' => [
                '/api/v1/nyt-bestsellers?isbn[]=9781451627282&isbn[]=9780399169274',
                200,
                [
                    "status" => "OK",
                    "num_results" => 2,
                    "results" => [
                        [
                            "title" => "11/22/63",
                            "author" => "Stephen King",
                        ],
                        [
                            "title" => "#GIRLBOSS",
                            "author" => "Sophia Amoruso",
                        ],
                    ]
                ]
            ],
            'valid isbn' => [
                '/api/v1/nyt-bestsellers?isbn[]=9781451627282',
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
                '/api/v1/nyt-bestsellers?isbn[]=DOESNOTEXIST',
                200,
                [
                    "status" => "OK",
                    "num_results" => 0,
                ],
                [
                    "status" => "OK",
                    "num_results" => 0,
                    "results" => [],
                ],
            ],
            'no isbn' => [
                '/api/v1/nyt-bestsellers',
                200,
                [
                    "num_results" => 5,
                ],
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

        $response = $this->getJson('/api/v1/nyt-bestsellers?isbn[]=FAIL');

        $response->assertStatus(200);
        $response->assertExactJson(["num_results"=> 0, "results" => [],"status"=> "OK"]);
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
        $response = $this->getJson('/api/v1/nyt-bestsellers?isbn[]=' . $isbn);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['isbn.0']);
    }

    #[Test]
    public function testInvalidIsbnShouldBeArray(): void
    {
        $response = $this->getJson('/api/v1/nyt-bestsellers?isbn=9781451627282,9780399169274');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['isbn']);
    }

    #[Test]
    public function testIsbnCacheMechanism(): void
    {
        Cache::flush();
        Http::fake([
            '*nytimes.com*' => Http::response([
                "status" => "OK",
                "num_results" => 1,
                "results" => [
                    [
                        "title" => "Cached Book",
                        "author" => "Cached Author",
                    ]
                ]
            ], 200),
        ]);

        $this->getJson('/api/v1/nyt-bestsellers?isbn[]=9781451627282');
        $this->assertTrue(Cache::has('nyt.bestsellers.' . md5(json_encode(['isbn' => '9781451627282']))));

        Http::fake([
            '*nytimes.com*' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/v1/nyt-bestsellers?isbn[]=9781451627282');
        $response->assertJsonFragment(["title" => "Cached Book"]);
    }
}
