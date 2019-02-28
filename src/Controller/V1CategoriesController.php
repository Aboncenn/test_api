<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Category;

/**
 * @Route("/v1/category")
 */
class V1CategoriesController extends Controller
{
    /**
     * @Route("", name="v1_category", methods="GET|OPTIONS")
     */
    public function category(Request $request): JsonResponse
    {
        $data = [
            'category' => []
        ];
        foreach ($this->getDoctrine()->getManager()->getRepository(Category::class)->findBy(['user' => null]) as $category) {
            array_push($data['category'], [
                'id' => $category->getId(),
                'name' => $category->getName()
            ]);
        }
        $code = Response::HTTP_OK;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{id}", name="v1_category_id", methods="GET|OPTIONS")
     */
    public function category_id(Request $request, Category $category): JsonResponse
    {
        // Non numeric value (HTTP 406)
        if (!is_numeric($request->attributes->get('id'))){
            $data = [
                'msg' => 'Non numeric value',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        }
        // Not Found (HTTP 404)
        elseif (is_null($category)){
            $data = [
                'msg' => 'Not Found',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_NOT_FOUND;
        }
        // Troll (HTTP 423)
        elseif (!is_null($category->getUser())){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://huit.re/o5exfCDu',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_LOCKED;
        // OK (HTTP 200)
        }else{
            $data = [
                'id' => $category->getId(),
                'name' => $category->getName()
            ];
            $code = Response::HTTP_OK;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }
}
