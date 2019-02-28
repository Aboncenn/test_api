<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
class V1GeocoderControllerTest extends WebTestCase
{
  /*
  public function testV1GeocoderAdressGET200()
  {
    $client = self::createClient();
    $client->request('GET', '/v1/geocoder/La Charbonnade/38000/Grenoble');
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }

  public function testV1GeocoderQueryGET200()
  {
    $client = self::createClient();
    $client->request('GET', '/v1/geocoder/lat=48.862725lon=2.287592');
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }
  */
  public function testSomething()
  {
      $this->assertTrue(true);
  }
}
