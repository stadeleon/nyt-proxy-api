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
            name: 'isbn[]',
            description: 'Filter by ISBN',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            style: 'form',
            explode: true
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
            description: "Success",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "OK"),
                    new OA\Property(property: "num_results", type: "integer", example: 2),
                    new OA\Property(
                        property: "results",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "title", type: "string", example: "11/22/63"),
                                new OA\Property(property: "author", type: "string", example: "Stephen King"),
                                new OA\Property(property: "description", type: "string", example: "An English teacher travels back to 1958..."),
                                new OA\Property(property: "contributor", type: "string", example: "by Stephen King"),
                                new OA\Property(property: "publisher", type: "string", example: "Pocket Books")
                            ]
                        )
                    )
                ],
                type: "object"
            )
        ),
        new OA\Response(
            response: 403,
            description: 'Unauthorized access'
        ),
        new OA\Response(
            response: 422,
            description: "Validation Error"
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
