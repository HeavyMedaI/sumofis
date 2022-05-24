<?php

namespace Makrosum\MeetingBundle\Repository;
use Makrosum\MeetingBundle\Abstractions\MakrosumRepository;
use Makrosum\MeetingBundle\Entity\Company;

/**
 * PersonnelRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonnelRepository extends MakrosumRepository
{
    public function getAllPersonnelsInfo(Company $Company)
    {
        $personnels = $this->findBy([
            "company" => $Company->getId()
        ]);

        $Personnels = [];

        foreach($personnels as $personnel){

            $dbSource = $this->createEntityQueryBuilder();
            $dbSource->select("user", "department", "position")
                ->from("Makrosum\MeetingBundle\Entity\User", "user")
                ->leftJoin(
                    "Makrosum\MeetingBundle\Entity\Department",
                    "department",
                    \Doctrine\ORM\Query\Expr\Join::WITH,
                    "department.id = :department_id"
                )
                ->leftJoin(
                    "Makrosum\MeetingBundle\Entity\Position",
                    "position",
                    \Doctrine\ORM\Query\Expr\Join::WITH,
                    "position.id = :position_id"
                )
                ->setParameters([
                    "personnel_id" => $personnel->getUser(),
                    "department_id" => $personnel->getDepartment(),
                    "position_id" => $personnel->getPosition()
                ])
                ->where("user.id = :personnel_id")
                ->orderBy("user.id");

            $Personnels[] = [
                "personnel" => $personnel,
                "storage" => $dbSource->getQuery()->getResult()
            ];

        }

        return $Personnels;

    }
}
