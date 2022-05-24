<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 8.03.2016
 * Time: 08:19
 */

namespace Makrosum\MeetingBundle\Abstractions;


use Doctrine\ORM\EntityManager;

class MakrosumEntity extends EntityManager
{
    public function __construct()
    {}
}