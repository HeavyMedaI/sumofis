<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * MeetingObject
 *
 * @ORM\Table(name="meeting_object")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\MeetingObjectRepository")
 */
class MeetingObject extends MakrosumEntity
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
     * @ORM\Column(name="creator", type="integer")
     */
    private $creator;

    /**
     * @var int
     *
     * @ORM\Column(name="company", type="integer")
     */
    private $company;

    /**
     * @var int
     *
     * @ORM\Column(name="meeting", type="integer")
     */
    private $meeting;

    /**
     * @var string
     *
     * @ORM\Column(name="resource", type="string")
     */
    private $resource;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator(User $creator)
    {
        $this->creator = $creator->getId();
        return $this;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany(Company $company)
    {
        $this->company = $company->getId();
        return $this;
    }

    public function getMeeting()
    {
        return $this->meeting;
    }

    public function setMeeting(Meeting $meeting)
    {
        $this->meeting = $meeting->getId();
        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }
}

