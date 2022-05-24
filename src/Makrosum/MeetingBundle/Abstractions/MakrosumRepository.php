<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 8.03.2016
 * Time: 17:08
 */

namespace Makrosum\MeetingBundle\Abstractions;

use Doctrine\ORM\EntityRepository;

abstract class MakrosumRepository extends EntityRepository
{
    public function getRepository($Entity, $Bundle = "MeetingBundle")
    {
        $explodeEntity = explode(":", $Entity);
        $Entity = $explodeEntity[count($explodeEntity)-1];
        $explodeEntity = explode("\\", $Entity);
        $Entity = $explodeEntity[count($explodeEntity)-1];
        return $this->getEntityManager()->getRepository("{$Bundle}:{$Entity}");
    }

    public function createEntityQueryBuilder()
    {
        return $this->getEntityManager()->createQueryBuilder();
    }
}