<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * MeetingMember
 *
 * @ORM\Table(name="meeting_member")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\MeetingMemberRepository")
 */
class MeetingMember extends MakrosumEntity
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
     * @var int
     *
     * @ORM\Column(name="meeting", type="integer")
     */
    private $meeting;

    /**
     * @var int
     *
     * @ORM\Column(name="user", type="integer", nullable=true)
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="user_email", type="string")
     */
    private $user_email;

    /**
     * @var int
     *
     * @ORM\Column(name="role", type="integer")
     */
    private $role; # 1: Member, 2: Reporter, 3: External Members

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

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user->getId();
        return $this;
    }

    public function getUserEmail()
    {
        return $this->user_email;
    }

    public function setUserEmail(User $user)
    {
        $this->user_email = $user->getEmail();
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }
}

