<?php

namespace GOG\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GOGCatalogBundle:Default:index.html.twig');
    }
}
