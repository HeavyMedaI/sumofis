<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * Activation
 *
 * @ORM\Table(name="activation")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\ActivationRepository")
 */
class Activation extends MakrosumEntity
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
     * @ORM\Column(name="user", type="integer")
     */
    private $user;


    /**
     * @var string
     *
     * @ORM\Column(name="token_key", type="string", length=255)
     */
    private $token_key;

    /**
     * @var string
     *
     * @ORM\Column(name="sms_key", type="string", nullable=true)
     */
    private $sms_key;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    public function getToken()
    {
        return $this->token_key;
    }

    public function setToken($token)
    {
        $this->token_key = $token;
        return $this;
    }

    public function getSmsKey()
    {
        return $this->sms_key;
    }

    public function setSmsKey($sms_key)
    {
        $this->sms_key = $sms_key;
        return $this;
    }
}

