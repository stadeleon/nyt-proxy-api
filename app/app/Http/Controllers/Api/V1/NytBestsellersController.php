<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Nyt\NytApiServiceContract;
use App\Http\Requests\Nyt\NytBestsellersRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/v1/nyt-bestsellers',
    summary: 'get bestsellers from NYT',
    parameters: [
        new OA\Parameter(
            name: 'author',
            description: 'Filter by Author',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'title',
            description: 'Filter by Title',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'isbn',
            description: 'Filter by ISBN',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'offset',
            description: 'Page offset',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Success',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(type: 'object')
                    )
                ],
                type: 'object'
            )
        ),
        new OA\Response(
            response: 403,
            description: 'Unauthorized access'
        )
    ]
)]
class NytBestsellersController extends BaseApiController
{
    public function __invoke(NytBestsellersRequest $request, NytApiServiceContract $service): JsonResponse
    {
        $params = $request->validated();

        return response()->json($service->getBestsellers($params));
    }
}
