<?php

namespace Tests\External\Nyt;

use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Group('external')]
class NytBestsellersExternalTest extends TestCase
{
    #[DataProvider('externalDataProvider')]
    #[Test]
    #[Group('external')]
    public function testBestsellersExternalScenarios(
        string $query,
        int $expectedStatus,
        array $expectedJsonFragment
    ): void {
        $response = $this->getJson($query);

        $response->assertStatus($expectedStatus);
        $response->assertJsonFragment($expectedJsonFragment);
    }

    public static function externalDataProvider(): array
    {
        return [
            'valid isbn external' => [
                '/api/v1/nyt-bestsellers?isbn=9781451627282',
                200,
                [
                    "status" => "OK",
                ],
            ],
            'missing isbn external' => [
                '/api/v1/nyt-bestsellers',
                200,
                [
                    "status" => "OK",
                ],
            ],
        ];
    }

    #[Test]
    #[Group('external')]
    public function testExternalScenarioWithoutMock(): void
    {
        $response = $this->getJson('/api/v1/nyt-bestsellers?isbn=9781451627282');
        $response->assertStatus(200);
    }
}
