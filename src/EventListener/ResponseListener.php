<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->set('Access-Control-Allow-Origin', '*');
        $event->getResponse()->headers->set('Access-Control-Allow-Headers', '*');
        $event->getResponse()->headers->set('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,PATCH,OPTIONS');
    }
}

?>
