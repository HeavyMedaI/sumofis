<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Makrosum\MeetingBundle\Abstractions\MakrosumEntity;

/**
 * Company
 *
 * @ORM\Table(name="company")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\CompanyRepository")
 */
class Company extends MakrosumEntity
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
     * @var integer
     *
     * @ORM\Column(name="owner", type="integer", length=90)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=90, unique=true)
     */
    private $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=90)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=90)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=90)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", nullable=true, length=90)
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=90, nullable=true)
     */
    private $website;

    /**
     * @var integer
     *
     * @ORM\Column(name="country", type="integer", length=90)
     */
    private $country;

    /**
     * @var integer
     *
     * @ORM\Column(name="province", type="integer", length=90)
     */
    private $province;

    /**
     * @var integer
     *
     * @ORM\Column(name="city", type="integer", length=90)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=90)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="map_coodinates", type="string", length=90, nullable=true)
     */
    private $map_coodinates;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=90, nullable=true, options={"default":"default.png"})
     */
    private $logo;

    /**
     * @ORM\Column(name="is_active", type="boolean", options={"default":true})
     */
    private $isActive;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function isActive($isActive=null){

        if($isActive!==null){

            $this->isActive = $isActive;

            return $this;

        }

        return $this->isActive;

    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(User $owner)
    {
        $this->owner = $owner->getId();
        return $this;
    }

    public function isOwner(User $user)
    {
        return $this->owner == $user->getId();
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
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

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($fax)
    {
        $this->fax = $fax;
        return $this;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    public function getProvince()
    {
        return $this->province;
    }

    public function setProvince($province)
    {
        $this->province = $province;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function getMap()
    {
        return $this->map_coodinates;
    }

    public function setMap($map_coodinates)
    {
        $this->map_coodinates = $map_coodinates;
        return $this;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }
}

