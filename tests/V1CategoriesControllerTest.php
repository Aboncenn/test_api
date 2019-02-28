<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class V1CategoriesControllerTest extends WebTestCase
{
  public function testV1CategoryGET200()
  {
    $client = self::createClient();

      $client->request('GET', '/v1/category');
      $this->assertEquals(200, $client->getResponse()->getStatusCode());

  }
  public function testV1CategoryGETid423()
  {
    $client = self::createClient();
      $client->request('GET', '/v1/category/5');
      $this->assertEquals(423, $client->getResponse()->getStatusCode());
  }
/*
  public function testV1CategoryGETid406()
  {
    $client = self::createClient();
      $client->request('GET', '/v1/category/rickroll');
      $this->assertEquals(406, $client->getResponse()->getStatusCode());
  }

  public function testV1CategoryGETid404()
  {
    $client = self::createClient();
      $client->request('GET', '/v1/category/9999999');
      $this->assertEquals(404, $client->getResponse()->getStatusCode());
  }
  */

  public function testV1CategoryGETid200()
  {
    $client = self::createClient();
      $client->request('GET', '/v1/category/4', []);
      $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }


}
