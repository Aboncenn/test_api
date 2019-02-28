<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use App\Entity\Category;

/**
 * @Route("/v2/category")
 */
class V2CategoriesController extends Controller
{

    protected function getUser(): User
    {
        return $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(['email' => parent::getUser()->getUsername()]);
    }

    /**
     * @Route("", name="v2_category", methods="GET")
     */
    public function category(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $data = [
            'category' => $this->getCategories($user->getId()),
        ];
        $code = Response::HTTP_OK;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    private function getCategories(int $user_id): array
      {
          $em = $this->getDoctrine()->getManager();
          $query =
              'SELECT
                  c.id,
                  c.name
              FROM category c
              WHERE (c.user_id IS NULL OR c.user_id = :user_id)';
          $stmt = $em->getConnection()->prepare($query);
          $stmt->bindValue('user_id', $user_id);
          $stmt->execute();
          return $stmt->fetchAll();
      }

    /**
     * @Route("", name="v2_category_create", methods="POST")
     */
    public function category_create(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $input = json_decode($request->getContent(), true);

        // Bad Request (HTTP 400)
        if (!array_key_exists('name', $input)){
            $data = ['msg' => 'No name provided'];
            $code = Response::HTTP_BAD_REQUEST;
        // Conflict (HTTP 409)
        }elseif(!is_null($em->getRepository(Category::class)->findOneBy(['name' => $input['name'], 'user' => $user])) || !is_null($em->getRepository(Category::class)->findOneBy(['name' => $input['name'], 'user' => null]))){
            $data = ['msg' => 'Already exists'];
            $code = Response::HTTP_CONFLICT;
        // Created (HTTP 201)
        }else{
            $category = new Category();
            $category->setUser($user);
            $category->setName($input['name']);
            $em->persist($category);
            $em->flush();
            $data = [
                'id' => $category->getId(),
                'name' => $category->getName()
            ];
            $code = Response::HTTP_CREATED;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{id}", name="v2_category_read", methods="GET")
     * #Troll : https://https://huit.re/YJVMczvb
     */
    public function category_read(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(Category::class)->findOneBy(['id' => $request->attributes->get('id')]);

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
        // Troll (HTTP 423)
        }elseif (!is_null($category->getUser()) && $category->getUser() != $this->getUser()){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://https://huit.re/YJVMczvb',
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

    /**
     * @Route("/{id}", name="v2_category_update", methods="PUT")
     * #Troll : https://huit.re/VUVgwTX5
     */
    public function category_update(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $category = $em->getRepository(Category::class)->findOneBy(['id' => $request->attributes->get('id')]);
        $input = json_decode($request->getContent(), true);
        $conflictCheck = $em->getRepository(Category::class)->findOneBy(['name' => $input['name'], 'user' => $user]);
        $conflictCheck2 = $em->getRepository(Category::class)->findOneBy(['name' => $input['name'], 'user' => null]);

        // Non numeric value (HTTP 406)
        if (!is_numeric($request->attributes->get('id'))){
            $data = [
                'msg' => 'Non numeric value',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        }
        // Bad Request (HTTP 400)
        elseif (!array_key_exists('name', $input)){
            $data = ['msg' => 'No name provided'];
            $code = Response::HTTP_BAD_REQUEST;
        // Conflict (HTTP 409)
        }elseif(($conflictCheck != $category && !is_null($conflictCheck)) || $conflictCheck2 != $category && !is_null($conflictCheck2)){
            $data = ['msg' => 'Already exists'];
            $code = Response::HTTP_CONFLICT;
        // Not Found (HTTP 404)
        }elseif (is_null($category)){
            $data = [
                'msg' => 'Not Found',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_NOT_FOUND;
        // Forbidden (HTTP 403)
        }elseif(is_null($category->getUser())){
            $data = [
                'msg' => 'Forbidden',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_FORBIDDEN;
        // Troll (HTTP 423)
        }elseif (!is_null($category->getUser()) && $category->getUser() != $this->getUser()){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://huit.re/VUVgwTX5',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_LOCKED;
        // Accepted (HTTP 202)
        }else{
            $category->setName($input['name']);
            $em->flush();
            $data = [
                'id' => $category->getId(),
                'name' => $category->getName()
            ];
            $code = Response::HTTP_ACCEPTED;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }

    /**
     * @Route("/{id}", name="v2_category_delete", methods="DELETE")
     * #Troll : https://huit.re/Q_pWyy6o
     */
    public function category_delete(Request $request): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $category = $em->getRepository(Category::class)->findOneBy(['id' => $request->attributes->get('id')]);

        // Non numeric value (HTTP 406)
        if (!is_numeric($request->attributes->get('id'))){
            $data = [
                'msg' => 'Non numeric value',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        // Not Found (HTTP 404)
        }elseif (is_null($category)){
            $data = [
                'msg' => 'Not Found',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_NOT_FOUND;
        // Forbidden (HTTP 403)
        }elseif(is_null($category->getUser())){
            $data = [
                'msg' => 'Forbidden',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_FORBIDDEN;
        // Not Acceptable (HTTP 406)
        }elseif(sizeof($category->getPlaces()) > 0){
            $data = [
                'msg' => 'Not Acceptable. Some places are using this category.',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_NOT_ACCEPTABLE;
        // Troll (HTTP 423)
        }elseif (!is_null($category->getUser()) && $category->getUser() != $this->getUser()){
            $data = [
                'msg' => 'Unauthorized, please read documentation at https://huit.re/Q_pWyy6o',
                'id' => $request->attributes->get('id')
            ];
            $code = Response::HTTP_LOCKED;
        // OK (HTTP 200)
        }else{
            $em->remove($category);
            $em->flush();
            $data = [];
            $code = Response::HTTP_OK;
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new JsonResponse($json, $code, [], true);
    }
}
