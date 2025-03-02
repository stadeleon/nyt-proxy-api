<?php

namespace Tests\Unit\Nyt;

use App\Contracts\Nyt\NytApiServiceContract;
use App\Proxy\Nyt\NytApiServiceProxy;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DummyNytApiService implements NytApiServiceContract
{
    public int $callCount = 0;

    public function getBestsellers(array $params): array
    {
        $this->callCount++;
        if (isset($params['isbn']) && $params['isbn'] === '9781451627282') {
            return [
                "status" => "OK",
                "results" => [
                    [
                        "title" => "11/22/63",
                        "author" => "Stephen King"
                    ]
                ]
            ];
        } else {
            return [
                "status" => "OK",
                "results" => [
                    [
                        "title" => "Another Book",
                        "author" => "Another Author"
                    ]
                ]
            ];
        }
    }
}

class NytApiServiceProxyTest extends TestCase
{
    #[Test]
    public function testProxyCachesResponse(): void
    {
        Cache::flush();

        $dummyService = new DummyNytApiService();
        $proxy = new NytApiServiceProxy($dummyService);

        $params = ['isbn' => '9781451627282'];

        $result1 = $proxy->getBestsellers($params);
        $result2 = $proxy->getBestsellers($params);

        $this->assertEquals($result1, $result2);

        $this->assertEquals(
            1,
            $dummyService->callCount,
            "Underlying service should be called only once due to caching."
        );

        Cache::flush();
        $result3 = $proxy->getBestsellers($params);
        $this->assertEquals(
            2,
            $dummyService->callCount,
            "After flushing cache, underlying service should be called again."
        );
    }

    #[Test]
    public function testDifferentParamsGenerateDifferentCacheKeys(): void
    {
        Cache::flush();

        $dummyService = new DummyNytApiService();
        $proxy = new NytApiServiceProxy($dummyService);

        $params1 = ['isbn' => '9781451627282'];
        $params2 = ['isbn' => '9781451627299'];

        $result1 = $proxy->getBestsellers($params1);
        $result2 = $proxy->getBestsellers($params2);

        $this->assertEquals(
            2,
            $dummyService->callCount,
            "Different parameters should generate different cache keys."
        );
        $this->assertNotEquals(
            $result1,
            $result2,
            "Results for different parameters should not be the same (if service returns different data)."
        );
    }
}
