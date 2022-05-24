<?php

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Province
 *
 * @ORM\Table(name="province")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\ProvinceRepository")
 */
class Province
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
     * @ORM\Column(name="plate", type="integer", length=11, unique=false)
     */
    private $plate;

    /**
     * @var int
     *
     * @ORM\Column(name="country_code", type="integer", length=11)
     */
    private $country_code;

    /**
     * @var int
     *
     * @ORM\Column(name="area_code", type="integer", length=10, unique=false)
     */
    private $area_code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60)
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

    public function getPlate()
    {
        return $this->plate;
    }

    public function setPlate($plate)
    {
        $this->plate = $plate;
        return $this;
    }

    public function getCountry()
    {
        return $this->country_code;
    }

    public function setCountry($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    public function getArea()
    {
        return $this->area_code;
    }

    public function setArea($area_code)
    {
        $this->area_code = $area_code;
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

