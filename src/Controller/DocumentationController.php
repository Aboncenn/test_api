<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
    /**
     * @Route("/documentation/{version}", name="documentation", methods="GET")
     */
    public function documentation(Request $request): Response
    {
        $version = $request->attributes->get('version');
        switch ($version) {
            case 'v1':
                $file = __DIR__.'/../../doc/v1.html';
                break;
            case 'v2':
                $file = __DIR__.'/../../doc/v2.html';
                break;
            default:
                $file = 'nope';
                break;
        }
        if (file_exists($file)){
            return new Response(file_get_contents($file));
        }
        throw new NotFoundHttpException();
    }
}
