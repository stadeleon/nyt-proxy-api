<?php

namespace App\Contracts\Nyt;

interface NytApiServiceContract
{
    public function getBestsellers(array $params): array;
}
