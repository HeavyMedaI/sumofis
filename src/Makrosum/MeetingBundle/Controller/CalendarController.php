<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.02.2016
 * Time: 15:10
 */

namespace Makrosum\MeetingBundle\Controller;

use Makrosum\MeetingBundle\Abstractions\BaseController;

class CalendarController extends BaseController
{
    public function indexAction(){
        $this->renderBase();
        //$this->setLocale("tr", array("tr", "en", "tr_TR", "en_US"));
        return $this->renderIt("MeetingBundle:Calendar:index.html.twig");
    }
}