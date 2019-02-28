<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/v2")
 */
class V2CORSController extends Controller
{

    private function cors(Request $request): JsonResponse
    {
        $code = Response::HTTP_OK;
        $json = json_encode('', JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{a}", name="v2_cors_a", methods="OPTIONS")
     */
    public function cors_a(Request $request): JsonResponse
    {
        return $this->cors($request);
    }

    /**
     * @Route("/{a}/{b}", name="v2_cors_ab", methods="OPTIONS")
     */
    public function cors_ab(Request $request): JsonResponse
    {
        return $this->cors($request);
    }

    /**
     * @Route("/{a}/{b}/{c}", name="v2_cors_abc", methods="OPTIONS")
     */
    public function cors_abc(Request $request): JsonResponse
    {
        return $this->cors($request);
    }

    /**
     * @Route("/{a}/{b}/{c}/{d}", name="v2_cors_abcd", methods="OPTIONS")
     */
    public function cors_abcd(Request $request): JsonResponse
    {
        return $this->cors($request);
    }
}
