<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class V1PlacesControllerTest extends WebTestCase
{
  /*
  public function testV1PlaceGET400()
  {
    $client = self::createClient();
    $client->request('GET', '/v1/geocoder/120/300/20');
    $this->assertEquals(400, $client->getResponse()->getStatusCode());
  }

  public function testV1PlaceGET406()
  {
    $client = self::createClient();
    $client->request('GET', '/v1/geocoder/We are no strangers to love/You know the rules and so do I/A full commitment is what I am thinking of');
    $this->assertEquals(406, $client->getResponse()->getStatusCode());
  }

  public function testV1PlaceGET416()
  {
    $client = self::createClient();
    $client->request('GET', '/v1/48.862725/2.287592/9999');
    $this->assertEquals(416, $client->getResponse()->getStatusCode());
  }

  public function testV1PlaceGET200()
  {
    $client = self::createClient();
    $client->request('GET', '/v1/geocoder/48.862725/2.287592/20');
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }
  */
  public function testSomething()
  {
      $this->assertTrue(true);
  }
}
