<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * Permission
 *
 * @ORM\Table(name="permission")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\PermissionRepository")
 */
class Permission extends MakrosumEntity
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
     * @var int
     *
     * @ORM\Column(name="company", type="integer")
     */
    private $company;

    /**
     * @var boolean
     *
     * @ORM\Column(name="personnel_super", type="boolean")
     */
    private $personnel_super;

    /**
     * @var boolean
     *
     * @ORM\Column(name="meeting_super", type="boolean")
     */
    private $meeting_super;

    /**
     * @var boolean
     *
     * @ORM\Column(name="meeting_create", type="boolean")
     */
    private $meeting_create;

    /**
     * @var boolean
     *
     * @ORM\Column(name="meeting_edit", type="boolean")
     */
    private $meeting_edit;

    /**
     * @var boolean
     *
     * @ORM\Column(name="meeting_status", type="boolean")
     */
    private $meeting_status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="meeting_delete", type="boolean")
     */
    private $meeting_delete;

    /**
     * @var boolean
     *
     * @ORM\Column(name="meeting_request", type="boolean")
     */
    private $meeting_request;

    /**
     * @var boolean
     *
     * @ORM\Column(name="meeting_matter", type="boolean")
     */
    private $meeting_matter;


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

    public function setUser($user)
    {
        $this->user = $user;
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

    private function grants($grant)
    {

        return [
            "GRANTED_SUPER_PERSONNEL" => "personnel_super",
            "GRANTED_SUPER_MEETING" => "meeting_super",
            "GRANTED_CREATE_MEETING" => "meeting_create",
            "GRANTED_EDIT_MEETING" => "meeting_edit",
            "GRANTED_STATUS_MEETING" => "meeting_status",
            "GRANTED_DELETE_MEETING"  => "meeting_delete",
            "GRANTED_REQUEST_MEETING" => "meeting_request",
            "GRANTED_MATTER_MEETING"  => "meeting_matter",
        ][$grant];

    }

    public function isGranted($permission){

        $PermissionToGrant = $this->grants($permission);

        return $this->{$PermissionToGrant};

    }

    public function setGrant($permission, $status = true){

        $PermissionToGrant = $this->grants($permission);

        return $this->{$PermissionToGrant} = $status;
        return $this;

    }

}

