<?php

namespace App\Controller;

use App\Arrobe\GeoCoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/v1/geocoder")
 */
class V1GeocoderController extends Controller
{
    /**
     * @Route("/{address}/{postalcode}/{city}", name="v1_geocoder_address_postalcode_city", methods="GET|OPTIONS")
     */
    public function geocoder_address_postalcode_city(Request $request, GeoCoder $geoCoder): JsonResponse
    {
        $geoCoder
            ->setAddress($request->attributes->get('address'))
            ->setPostalCode($request->attributes->get('postalcode'))
            ->setCity($request->attributes->get('city'))
        ;
        $lat = $geoCoder->getLat();
        $lng = $geoCoder->getLng();
        if (!is_null($lat) && !is_null($lng)){
            $data = [
                'lat' =>  $lat,
                'lng' => $lng,
            ];
            $code = Response::HTTP_OK;
        }else{
            $data = [
                'msg' => 'Not Found'
            ];
            $code = Response::HTTP_NOT_FOUND;
        }
        $data['address'] = $request->attributes->get('address');
        $data['postalcode'] = $request->attributes->get('postalcode');
        $data['city'] = $request->attributes->get('city');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{query}", name="v1_geocoder_query", methods="GET|OPTIONS")
     */
    public function geocoder_query(Request $request, GeoCoder $geoCoder): JsonResponse
    {
        $geoCoder->setQuery($request->attributes->get('query'));
        $lat = $geoCoder->getLat();
        $lng = $geoCoder->getLng();
        if (!is_null($lat) && !is_null($lng)){
            $data = [
                'lat' =>  $lat,
                'lng' => $lng,
            ];
            $code = Response::HTTP_OK;
        }else{
            $data = [
                'msg' => 'Not Found'
            ];
            $code = Response::HTTP_NOT_FOUND;
        }
        $data['query'] = $request->attributes->get('query');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }
}
