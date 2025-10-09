<?php

namespace OrderMinimumAmount\Controller;

use OpenApi\Controller\Front\BaseFrontOpenApiController;
use OpenApi\Service\OpenApiService;
use OrderMinimumAmount\OrderMinimumAmount;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/open_api')]
class ApiController extends BaseFrontOpenApiController
{
    #[Route('/order_minimum_amount', name: 'get_order_minimum_amount')]
    #[OA\Get(
        path: "/order_minimum_amount",
        summary: "Get order minimum amount",
        tags: ["Order minimum amount"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful",
                content: new OA\JsonContent(type:"integer")
            )
        ]
    )]
    public function getOrderMinimumAmount() {
        $minAmount =  OrderMinimumAmount::getConfigValue('minimum_amount');
        return OpenApiService::jsonResponse(intval($minAmount));
    }
}
