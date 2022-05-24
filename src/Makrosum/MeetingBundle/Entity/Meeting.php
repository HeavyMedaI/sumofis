<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * Meeting
 *
 * @ORM\Table(name="meeting")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\MeetingRepository")
 */
class Meeting extends MakrosumEntity
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
     * @var string
     *
     * @ORM\Column(name="header", type="string")
     */
    private $header;

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
     * @ORM\Column(name="category", type="integer")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="begin", type="datetime")
     */
    private $begin;

    /**
     * @var string
     *
     * @ORM\Column(name="end", type="datetime")
     */
    private $end;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status; # 1: Created, 2: Completed, 3: Canceled, 4: Request

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    public function isCreator(User $user)
    {
        return $this->creator == $user->getId();
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

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(MeetingCategory $category)
    {
        $this->category = $category->getId();
        return $this;
    }

    public function getBegin()
    {
        return $this->begin;
    }

    public function setBegin($begin)
    {
        if(is_object($begin)&&get_class($begin)=="DateTime"){
            $this->begin = $begin;
        }else if(is_string($begin)){
            $this->begin = new \DateTime($begin);
        }
        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd($end)
    {
        if(is_object($end)&&get_class($end)=="DateTime"){
            $this->end = $end;
        }else if(is_string($end)){
            $this->end = new \DateTime($end);
        }
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

}

