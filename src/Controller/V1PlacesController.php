<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/v1/places")
 */
class V1PlacesController extends Controller
{
    /**
     * @Route("/{latitude}/{longitude}/{range}", name="v1_places_latitude_longitude_range", methods="GET|OPTIONS")
     */
    public function places_latitude_longitude_range(Request $request): JsonResponse
    {
        $lat = floatval($request->attributes->get('latitude'));
        $lng = floatval($request->attributes->get('longitude'));
        $range = floatval($request->attributes->get('range'));

        $baseData = [
            'latitude' => $lat,
            'longitude' => $lng,
            'range' => $range
        ];

        // Invalid coordinates (HTTP 400)
        if ($lat > 90 || $lat < -90 || $lng > 180 || $lng < -180){
            $data = array_merge([
                'msg' => 'Invalid coordinates'
            ], $baseData);
            $code = Response::HTTP_BAD_REQUEST;
        // Non numeric value (HTTP 406)
        }elseif (!is_numeric($request->attributes->get('latitude')) || !is_numeric($request->attributes->get('longitude')) || !is_numeric($request->attributes->get('range'))){
            $data = array_merge([
                'msg' => 'Non numeric value'
            ], $baseData);
            $code = Response::HTTP_NOT_ACCEPTABLE;
        // Range too big (HTTP 416)
        }elseif ($range > 100){
            $data = array_merge([
                'msg' => 'Range too big'
            ], $baseData);
            $code = Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE;
        // OK (HTTP 200)
        }else{
            $data = array_merge($baseData, [
                'results' => $this->getPlacesInRange($lat, $lng, $range)
            ]);
            $code = Response::HTTP_OK;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    private function getPlacesInRange(float $latitude, float $longitude, float $range): array
    {
        $em = $this->getDoctrine()->getManager();
        $query =
            'SELECT
                p.name,
                p.address,
                p.postal_code,
                p.city,
                p.latitude,
                p.longitude,
                p.category_id
            FROM place p
            WHERE p.user_id IS NULL
            HAVING (6371 * ACOS(
                    COS(RADIANS(:latitude))
                    * COS(RADIANS(p.latitude))
                    * COS(RADIANS(p.longitude) - RADIANS(:longitude))
                    + SIN(RADIANS(:latitude))
                    * SIN(RADIANS(p.latitude))
                )
            ) < :range';
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('latitude', $latitude);
        $stmt->bindValue('longitude', $longitude);
        $stmt->bindValue('range', $range);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
