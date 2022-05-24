<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.02.2016
 * Time: 15:31
 */

namespace Makrosum\MeetingBundle\Controller;

use Makrosum\MeetingBundle\Abstractions\BaseController;

class TranslationTestController extends BaseController
{
    public function testAction(){

        $this->renderBase();

        $this->setLocale("en");

        //$string = $this->get("translator")->trans("string.email", array(), "security", $locale);

        $this->data(array("email" => "string.email"));

        //var_dump($locale);
        //var_dump($string);

        return $this->renderIt("MeetingBundle:Trans:test.html.twig");
    }
}