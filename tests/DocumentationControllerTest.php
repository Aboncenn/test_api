<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class DocumentationControllerTest extends WebTestCase
{
  /* @TODO Make this test work
  public function testDocumentationGET($url)
  {
    $client = static::createClient();

      $client->request('GET', '/v2/category');
      $this->assertEquals(200, $client->getResponse()->getStatusCode());

  }

    public function urlProvider()
    {
      yield ['/documentation/v1'];
      yield ['/documentation/v2'];
    }
    */
    public function testSomething()
    {
        $this->assertTrue(true);
    }
}
