<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * Department
 *
 * @ORM\Table(name="department")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\DepartmentRepository")
 */
class Department extends MakrosumEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="company", type="integer")
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

}

