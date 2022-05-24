<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserSettings
 *
 * @ORM\Table(name="user_settings")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\UserSettingsRepository")
 */
class UserSettings
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
     * @ORM\Column(name="user_id", type="integer", unique=true)
     */
    private $user_id;


    /**
     *
     * @ORM\Column(type="string", length=60, unique=false, options={"default":"tr"})
     */
    private $language;

    /**
     * @var bool
     *
     * @ORM\Column(name="email_notification", type="boolean", options={"default":true})
     */
    private $email_notification;

    /**
     * @var bool
     *
     * @ORM\Column(name="sms_notification", type="boolean", options={"default":true})
     */
    private $sms_notification;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUserId(){
        return $this->user_id;
    }

    public function setUserId($user_id){
        $this->user_id = $user_id;
        return $this;
    }

    public function getLanguage(){
        return $this->language;
    }

    public function setLanguage($language){
        $this->language = $language;
        return $this;
    }

    public function getEmailNotification(){
        return $this->email_notification;
    }

    public function setEmailNotification($email_notification){
        $this->email_notification = $email_notification;
        return $this;
    }

    public function getSmsNotification(){
        return $this->sms_notification;
    }

    public function setSmsNotification($sms_notification){
        $this->sms_notification = $sms_notification;
        return $this;
    }

}
