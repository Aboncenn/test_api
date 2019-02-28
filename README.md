# Démarrage à partir du projet existant

1. `git clone https://gitlab.com/EPSIGre/cours-api-tests`
2. `cp .env.dist .env`
3. Configurer le fichier `.env`
4. `php bin/console doctrine:database:create`
5. `php bin/console make:migration`
6. `php bin/console doctrine:migrations:migrate -n`
7. `php bin/console app:create:cors-user`
8. `php bin/console app:create:token foo.bar@example.com` **Récupérer le token généré**
9. Configurer le vhost apache pour pointer sur le dossier `public/`

# Faire des tests

- [Documentation Symfony 4.2](https://symfony.com/doc/current/testing.html)
- [Configurer la base de données pour les tests](https://symfony.com/doc/current/testing/database.html#changing-database-settings-for-functional-tests)
- [API v2 Token Header](https://github.com/symfony/browser-kit/blob/v4.2.0/Client.php#L362)
- [Assertions de PHPUnit](https://phpunit.readthedocs.io/en/7.4/assertions.html)

## Démarrage

1. `composer require --dev symfony/phpunit-bridge`
2. `composer require --dev symfony/browser-kit`

## Exemple

- Pour la route `/v2/category`, le code est placé sous le dossier `tests` *(à vous de vous organiser sous le dossier tests)*.
- Le nom du fichier doit être identique au nom de la classe dans laquelle se trouve le test, par exemple : `V2CategoriesControllerTest.php` va avec `class V2CategoriesControllerTest`.
- Pour chaque route, il faut tester tous les cas, dans le cas présent, on peut recevoir un `200 HTTP OK` ou un `401 HTTP UNAUTHORIZED`.
- Il faut également vérifier les données reçues : vérifier qu'on reçoit bien le JSON attendu.

Code de `V2CategoriesControllerTest.php` avec cette clé d'API : `29c9e0a5cd4dd33325696e0d01371a6065db217bb68274ecb3c0077a1eb5d991`

```php
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class V2CategoriesControllerTest extends WebTestCase
{
    public function testGetCategoryWithoutAuth()
    {
        $client = static::createClient();
        $client->request('GET', '/v2/category');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $this->assertEquals('{"msg":"Invalid credentials."}', $client->getResponse()->getContent());
    }

    public function testGetCategoryWithAuth()
    {
        $client = static::createClient();
        $client->request('GET', '/v2/category', [], [], ['HTTP_apikey' => '29c9e0a5cd4dd33325696e0d01371a6065db217bb68274ecb3c0077a1eb5d991']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('{"category":[]}', $client->getResponse()->getContent());
    }
}

?>
```
