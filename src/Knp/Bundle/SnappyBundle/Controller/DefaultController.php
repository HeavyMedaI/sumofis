<?php

namespace Knp\Bundle\SnappyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('KnpSnappyBundle:Default:index.html.twig');
    }
}
