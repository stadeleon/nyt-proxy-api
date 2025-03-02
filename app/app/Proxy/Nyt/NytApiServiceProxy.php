<?php

namespace App\Proxy\Nyt;

use App\Contracts\Nyt\NytApiServiceContract;
use Illuminate\Support\Facades\Cache;

class NytApiServiceProxy implements NytApiServiceContract
{
    public function __construct(private readonly NytApiServiceContract $service)
    {
    }

    public function getBestsellers(array $params): array
    {
//        abort_if(auth()->guest(), 403);

        $key = 'nyt.bestsellers.' . md5(json_encode($params));
        return Cache::remember($key, 10, fn() => $this->service->getBestsellers($params));
    }
}
