<?php

namespace Makrosum\MeetingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $_host_name = $this->get('router')->getContext()->getHost();

        return $this->render("MeetingBundle:Default:index.html.twig", [
            "base_dir" => realpath($this->container->getParameter("kernel.root_dir")."/.."),
            "host_name" => $this->get('router')->getContext()->getHost(),
            "menu" => array(
                array("name" => "Men� 1"),
                array("name" => "Men� 2")
            )
        ]);
    }

    public function yandexAction(){
        exit("b5cef27e16c4");
    }
}
