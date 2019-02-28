<?php

namespace App\Controller;

use App\Arrobe\GeoCoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Place;

/**
 * @Route("/v2/places")
 */
class V2PlacesController extends Controller
{
    protected function getUser(): User
    {
        return $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(['email' => parent::getUser()->getUsername()]);
    }

    private function array_keys_exists(array $keys, array $array): bool
    {
        $r = true;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)){
                $r = false;
            }
        }
        return $r;
    }

    private function array_check_null(array $keys, array $array): bool
    {
        $r = false;
        foreach ($keys as $key) {
            if (is_null($array[$key])){
                $r = true;
            }
        }
        return $r;
    }

    /**
     * @Route("/{latitude}/{longitude}/{range}", name="v2_places_latitude_longitude_range", methods="GET")
     */
    public function places_latitude_longitude_range(Request $request): JsonResponse
    {
        $lat = floatval($request->attributes->get('latitude'));
        $lng = floatval($request->attributes->get('longitude'));
        $range = floatval($request->attributes->get('range'));
        $user = $this->getUser();

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
                'results' => $this->getPlacesInRange($lat, $lng, $range, $user->getId())
            ]);
            $code = Response::HTTP_OK;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    private function getPlacesInRange(float $latitude, float $longitude, float $range, int $user_id): array
    {
        $em = $this->getDoctrine()->getManager();
        $query =
            'SELECT
                p.id,
                p.name,
                p.address,
                p.postal_code,
                p.city,
                p.latitude,
                p.longitude,
                p.category_id,
                (
                    6371 * ACOS(
                        COS(RADIANS(:latitude))
                        * COS(RADIANS(p.latitude))
                        * COS(RADIANS(p.longitude) - RADIANS(:longitude))
                        + SIN(RADIANS(:latitude))
                        * SIN(RADIANS(p.latitude))
                    )
                ) AS distance
            FROM place p
            WHERE (p.user_id IS NULL OR p.user_id = :user_id)
            HAVING distance < :range
            ORDER BY distance';
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('latitude', $latitude);
        $stmt->bindValue('longitude', $longitude);
        $stmt->bindValue('range', $range);
        $stmt->bindValue('user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @Route("/category/{id}", name="v2_places_by_category", methods="GET")
     * #Troll : https://huit.re/f9kk-vvU
     */
    public function places_by_category(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(Category::class)->findOneBy(['id' => $request->attributes->get('id')]);
        $user = $this->getUser();

        // Not found (HTTP 404)
        if (is_null($category)){
            $data = [
                'msg' => 'Not Found',
                'id' => $category->getId()
            ];
            $code = Response::HTTP_NOT_FOUND;
        // Troll (HTTP 423)
        }elseif($category->getUser() != $user && !is_null($category->getUser())){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://huit.re/f9kk-vvU',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_LOCKED;
        // OK (HTTP 200)
        }else{
            $data = [
                'category_id' => $category->getId(),
                'place' => $this->getPlaceByCategory($user->getId(), $category->getId()),
            ];
            $code = Response::HTTP_OK;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    private function getPlaceByCategory(int $user_id, int $category_id): array
    {
        $em = $this->getDoctrine()->getManager();
        $query =
            'SELECT
                p.id,
                p.name,
                p.address,
                p.postal_code,
                p.city,
                p.latitude,
                p.longitude
            FROM place p
            WHERE p.category_id = :category_id
            AND (p.user_id IS NULL OR p.user_id = :user_id)';
        $stmt = $em->getConnection()->prepare($query);
        $stmt->bindValue('user_id', $user_id);
        $stmt->bindValue('category_id', $category_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @Route("", name="v2_place_create", methods="POST")
     * #Troll : https://huit.re/gm3Zxf2Q
     */
    public function place_create(Request $request, GeoCoder $geoCoder): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $input = json_decode($request->getContent(), true);

        // Bad Request (HTTP 400)
        if (!$this->array_keys_exists(['category_id', 'name', 'address', 'postal_code', 'city'], $input)){
            $data = ['msg' => 'Missing property : category_id or name or address, or postal_code or city'];
            $code = Response::HTTP_BAD_REQUEST;
        // Not Acceptable (HTTP 406)
        }elseif(!is_numeric($input['category_id'])){
            $data = ['msg' => 'Non numeric value on category_id'];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        // Not Found (HTTP 404) (Category)
        }elseif(is_null($em->getRepository(Category::class)->findOneBy(['id' => $input['category_id']]))){
            $data = ['msg' => 'Not Found'];
            $code = Response::HTTP_NOT_FOUND;
        // Conflict (HTTP 409)
        }elseif(!is_null($em->getRepository(Place::class)->findOneBy(['name' => $input['name'], 'user' => $user])) || !is_null($em->getRepository(Place::class)->findOneBy(['name' => $input['name'], 'user' => null]))){
            $data = ['msg' => 'Already exists'];
            $code = Response::HTTP_CONFLICT;
        // Troll (HTTP 423)
        }elseif (!is_null($em->getRepository(Category::class)->findOneBy(['id' => $input['category_id']])->getUser()) && $em->getRepository(Category::class)->findOneBy(['id' => $input['category_id']])->getUser() != $this->getUser()){
            $data = ['msg' => 'Unauthorized, please read documentation at https://huit.re/gm3Zxf2Q'];
            $code = Response::HTTP_LOCKED;
        // Created (HTTP 201)
        }else{
            $place = new Place();
            $place->setCategory($em->getRepository(Category::class)->findOneBy(['id' => $input['category_id']]));
            $place->setName($input['name']);
            $place->setAddress($input['address']);
            $place->setPostalCode($input['postal_code']);
            $place->setCity($input['city']);
            $place->setUser($user);
            $geoCoder
                ->setAddress($input['address'])
                ->setPostalCode($input['postal_code'])
                ->setCity($input['city'])
            ;
            $place->setLatitude($geoCoder->getLat());
            $place->setLongitude($geoCoder->getLng());
            $em->persist($place);
            $em->flush();
            $data = [
                'id' => $place->getId(),
                'category_id' => $place->getCategory()->getId(),
                'name' => $place->getName(),
                'address' => $place->getAddress(),
                'postal_code' => $place->getPostalCode(),
                'city' => $place->getCity(),
                'latitude' => $place->getLatitude(),
                'longitude' => $place->getLongitude()
            ];
            $code = Response::HTTP_CREATED;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{id}", name="v2_place_read", methods="GET")
     * #Troll : https://huit.re/8E9N_Yqs
     */
    public function place_read(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $place = $em->getRepository(Place::class)->findOneBy(['id' => $request->attributes->get('id')]);

        // Not Acceptable (HTTP 406)
        if(!is_numeric($request->attributes->get('id'))){
            $data = ['msg' => 'Non numeric value'];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        // Not Found (HTTP 404)
        }elseif (is_null($place)){
            $data = ['msg' => 'Not Found'];
            $code = Response::HTTP_NOT_FOUND;
        // Troll (HTTP 423)
        }elseif($place->getUser() != $user){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://huit.re/8E9N_Yqs',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_LOCKED;
        // OK (HTTP 200)
        }else{
            $data = [
                'id' => $place->getId(),
                'category_id' => $place->getCategory()->getId(),
                'name' => $place->getName(),
                'address' => $place->getAddress(),
                'postal_code' => $place->getPostalCode(),
                'city' => $place->getCity(),
                'latitude' => $place->getLatitude(),
                'longitude' => $place->getLongitude(),
            ];
            $code = Response::HTTP_OK;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{id}", name="v2_place_update", methods="PATCH")
     * #Troll : https://huit.re/wfcM64Qa
     */
    public function place_update(Request $request, GeoCoder $geoCoder): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $place = $em->getRepository(Place::class)->findOneBy(['id' => $request->attributes->get('id')]);
        $input = json_decode($request->getContent(), true);

        // Not Found (HTTP 404)
        if (is_null($place)){
            $data = ['msg' => 'Not Found'];
            $code = Response::HTTP_NOT_FOUND;
        // Forbidden (HTTP 403)
        }elseif(is_null($place->getUser())){
            $data = ['msg' => 'Forbidden'];
            $code = Response::HTTP_FORBIDDEN;
        // Not Acceptable (HTTP 406)
        }elseif(!is_numeric($request->attributes->get('id'))){
            $data = ['msg' => 'Non numeric value'];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        // Troll (HTTP 423)
        }elseif($place->getUser() != $user){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://huit.re/wfcM64Qa',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_LOCKED;
        // OK (HTTP 200)
        }else{
            $error = false;
            if (array_key_exists('category_id', $input)){
                $category = $em->getRepository(Category::class)->findOneBy(['id' => $input['category_id']]);
                if (is_null($category)){
                    $data = [
                        'msg' => 'Invalid category_id',
                    ];
                    $code = Response::HTTP_BAD_REQUEST;
                    $error = true;
                }
                $place->setCategory($category);
            }
            if (array_key_exists('name', $input)){
                $conflictCheck = $em->getRepository(Place::class)->findOneBy(['name' => $input['name'], 'user' => $user]);
                if ($conflictCheck != $place && !is_null($conflictCheck)){
                    $data = [
                        'msg' => 'Already exists',
                    ];
                    $code = Response::HTTP_CONFLICT;
                    $error = true;
                }
                $place->setName($input['name']);
            }
            $geoUpdate = false;
            if (array_key_exists('address', $input)){
                $place->setAddress($input['address']);
                $geoUpdate = true;
            }
            if (array_key_exists('postal_code', $input)){
                $place->setPostalCode($input['postal_code']);
                $geoUpdate = true;
            }
            if (array_key_exists('city', $input)){
                $place->setCity($input['city']);
                $geoUpdate = true;
            }
            if (array_key_exists('latitude', $input)){
                if (!array_key_exists('longitude', $input)){
                    $data = [
                        'msg' => 'Latitude can not be updated without longitude',
                    ];
                    $code = Response::HTTP_BAD_REQUEST;
                    $error = true;
                }
                if (!is_numeric($input['latitude'])){
                    $data = [
                        'msg' => 'Latitude is not a number',
                    ];
                    $code = Response::HTTP_PRECONDITION_FAILED;
                    $error = true;
                }
                if (!$error){
                    $place->setLatitude($input['latitude']);
                }
                $geoUpdate = false;
            }
            if (array_key_exists('longitude', $input)){
                if (!array_key_exists('latitude', $input)){
                    $data = [
                        'msg' => 'Longitude can not be updated without latitude',
                    ];
                    $code = Response::HTTP_BAD_REQUEST;
                    $error = true;
                }
                if (!is_numeric($input['longitude'])){
                    $data = [
                        'msg' => 'Longitude is not a number',
                    ];
                    $code = Response::HTTP_PRECONDITION_FAILED;
                    $error = true;
                }
                if (!$error){
                    $place->setLongitude($input['longitude']);
                }
                $geoUpdate = false;
            }
            // Si pas d'erreur...
            if (!$error){
                if ($geoUpdate){
                    $geoCoder
                        ->setAddress($place->getAddress())
                        ->setPostalCode($place->getPostalCode())
                        ->setCity($place->getCity())
                    ;
                    $place->setLatitude($geoCoder->getLat());
                    $place->setLongitude($geoCoder->getLng());
                }
                $em->flush();
                $data = [
                    'id' => $place->getId(),
                    'category_id' => $place->getCategory()->getId(),
                    'name' => $place->getName(),
                    'address' => $place->getAddress(),
                    'postal_code' => $place->getPostalCode(),
                    'city' => $place->getCity(),
                    'latitude' => $place->getLatitude(),
                    'longitude' => $place->getLongitude()
                ];
                $code = Response::HTTP_ACCEPTED;
            }
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{id}", name="v2_place_delete", methods="DELETE")
     * #Troll : https://huit.re/HLCBv6-c
     */
    public function place_delete(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $place = $em->getRepository(Place::class)->findOneBy(['id' => $request->attributes->get('id')]);

        // Non numeric value (HTTP 406)
        if (!is_numeric($request->attributes->get('id'))){
            $data = [
                'msg' => 'Non numeric value'
            ];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        // Not Found (HTTP 404)
        }elseif (is_null($place)){
            $data = [
                'msg' => 'Not Found'
            ];
            $code = Response::HTTP_NOT_FOUND;
        // Forbidden (HTTP 403)
        }elseif(is_null($place->getUser())){
            $data = [
                'msg' => 'Forbidden'
            ];
            $code = Response::HTTP_FORBIDDEN;
        // Troll (HTTP 423)
        }elseif (!is_null($place->getUser()) && $place->getUser() != $this->getUser()){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://huit.re/HLCBv6-c',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_LOCKED;
        // OK (HTTP 200)
        }else{
            $em->remove($place);
            $em->flush();
            $data = [];
            $code = Response::HTTP_OK;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

}
