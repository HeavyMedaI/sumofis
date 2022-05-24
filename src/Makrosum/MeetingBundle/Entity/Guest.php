<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Guest
 *
 * @ORM\Table(name="guest")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\GuestRepository")
 */
class Guest
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
     * @ORM\Column(name="token_key", type="string", length=255)
     */
    private $token_key;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="ref", type="integer")
     */
    private $ref;

    /**
     * @var integer
     *
     * @ORM\Column(name="personnel", type="integer", nullable=true)
     */
    private $personnel;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true, options={"default":"0"})
     */
    private $status;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getToken()
    {
        return $this->token_key;
    }

    public function setToken($token)
    {
        $this->token_key = $token;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function setRef($ref)
    {
        $this->ref = $ref;
        return $this;
    }

    public function getPersonnel()
    {
        return $this->personnel;
    }

    public function setPersonnel($personnel)
    {
        $this->personnel = $personnel;
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

