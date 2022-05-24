<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 11.03.2016
 * Time: 21:32
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\BaseController;

class UserController extends BaseController
{
    public function findUserByEmailAction()
    {
        $this->renderBase();

        exit(self::class."::findUserByEmail({$personnelEmail})");
    }
}