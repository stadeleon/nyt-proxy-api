<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "My NYT Bestsellers API",
)]
#[OA\Server(
    url: "http://localhost/api",
    description: "Local API server"
)]
class BaseApiController extends Controller
{

}
