<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use App\Entity\User;
use App\Entity\Category;

class V2CategoriesControllerTest extends WebTestCase
{
  public function testV2CategoryGET401()
  {
      $client = static::createClient();
      $client->request('GET', '/v2/category');
      $this->assertEquals(401, $client->getResponse()->getStatusCode());
  }


  public function testV2CategoryGET200()
  {
      $client = static::createClient();
      $client->request('GET', '/v2/category', [], [], ['HTTP_apikey' => '64188ef2731e812cb6ffe7ac4de8b69e90e5e148cf8ffb0d90aee231ef48dc77']);
      $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }

  public function testV2CategoryIdGET401()
  {
      $client = static::createClient();
      $client->request('GET', '/v2/category/5');
      $this->assertEquals(401, $client->getResponse()->getStatusCode());
  }

  public function testV2CategoryIdGET200()
  {
      $client = static::createClient();
      $client->request('GET', '/v2/category/5', [], [], ['HTTP_apikey' => '64188ef2731e812cb6ffe7ac4de8b69e90e5e148cf8ffb0d90aee231ef48dc77']);
      $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }
/*

  public function testV2CategoryPOST400(){

    $client = static::createClient();
    $client->request('POST', '/v2/category', [], [], ['HTTP_apikey' => '64188ef2731e812cb6ffe7ac4de8b69e90e5e148cf8ffb0d90aee231ef48dc77']);

    $this->assertEquals(400, $client->getResponse()->getStatusCode());
  }

  public function testV2CategoryPOST409(){

    $client = static::createClient();
    $client->request('POST', '/v2/category', array( ['name' => 'Category with my email !', 'user' => 2]), [], ['HTTP_apikey' => '64188ef2731e812cb6ffe7ac4de8b69e90e5e148cf8ffb0d90aee231ef48dc77']);

    $this->assertEquals(400, $client->getResponse()->getStatusCode());
  }
  public function testV2CategoryPOST200(){

    $client = static::createClient();
    $client->request('POST', '/v2/category', ['name' => 'Coucou je suis un test', 'user' => null], [], ['HTTP_apikey' => '64188ef2731e812cb6ffe7ac4de8b69e90e5e148cf8ffb0d90aee231ef48dc77']);

    $this->assertEquals(400, $client->getResponse()->getStatusCode());
  }

*/

}
