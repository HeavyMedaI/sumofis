<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * MeetingReport
 *
 * @ORM\Table(name="meeting_report")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\MeetingReportRepository")
 */
class MeetingReport extends MakrosumEntity
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
     * @var int
     *
     * @ORM\Column(name="subject", type="integer", nullable=false, options={"default":0})
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;


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
        $this->subject = 0;
        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject(MeetingMatterSubject $subject)
    {
        $this->subject = $subject->getId();
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

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
}

