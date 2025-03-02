<?php

namespace App\Services\Nyt;

use App\Contracts\Nyt\NytApiServiceContract;
use Illuminate\Support\Facades\Http;

class NytApiService implements NytApiServiceContract
{
    private string $url = 'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.nyt.api_key');
    }
    public function getBestsellers(array $params): array
    {
        $params['api-key'] = $this->apiKey;
        $response = Http::get($this->url, $params);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}
