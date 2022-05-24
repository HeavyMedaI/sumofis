<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 6.01.2016
 * Time: 13:07
 */

namespace Makrosum\MeetingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Makrosum\MeetingBundle\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max = 4096)
     */
    private $plainPassword;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt
     *
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="phone", type="string", length=60, unique=true, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(name="role", type="string", length=150)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=60, unique=false)
     */
    private $fullname;

    /**
     * @ORM\Column(name="country", type="string", length=60, unique=false)
     */
    private $country;

    /**
     * @ORM\Column(name="province", type="string", length=60, unique=false)
     */
    private $province;

    /**
     * @ORM\Column(name="city", type="string", length=60, unique=false)
     */
    private $city;

    /**
     * @ORM\Column(name="is_confirmed_email", type="boolean", options={"default":false})
     */
    private $isConfirmedEmail;

    /**
     * @ORM\Column(name="is_confirmed_phone", type="boolean", options={"default":false})
     */
    private $isConfirmedPhone;

    /**
     * @ORM\Column(name="is_active", type="boolean", options={"default":false})
     */
    private $isActive;

    public function __construct()
    {
        $this->isActive = true;
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid(null, true));
    }

    public function isActive($isActive=null){

        if($isActive!=null){

            $this->isActive = $isActive;

            return;

        }

        return $this->isActive;

    }

    public function getId(){

        return $this->id;

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

    public function getUsername()
    {
        return $this->email;
    }

    public function setUsername($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles(){

        return array($this->role);

    }

    public function getFullname(){

        return $this->fullname;

    }

    public function setFullname($fullname){

        $this->fullname = $fullname;
        return $this;

    }

    public function getCountry(){

        return $this->country;

    }

    public function setCountry($country){

        $this->country = $country;
        return $this;

    }

    public function getProvince(){

        return $this->province;

    }

    public function setProvince($province){

        $this->province = $province;
        return $this;

    }

    public function getCity(){

        return $this->city;

    }

    public function setCity($city){

        $this->city = $city;
        return $this;

    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize(){

        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));

    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }


    /**
     * Set role
     *
     * @param string $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isConfirmedEmail
     *
     * @param boolean $isConfirmedEmail
     *
     * @return User
     */
    public function setIsConfirmedEmail($isConfirmedEmail)
    {
        $this->isConfirmedEmail = $isConfirmedEmail;
        return $this;
    }

    /**
     * Get isConfirmedEmail
     *
     * @return boolean
     */
    public function getIsConfirmedEmail()
    {
        return $this->isConfirmedEmail;
    }

    /**
     * Set isConfirmedPhone
     *
     * @param boolean $isConfirmedEmail
     *
     * @return User
     */
    public function setIsConfirmedPhone($isConfirmedPhone)
    {
        $this->isConfirmedPhone = $isConfirmedPhone;
        return $this;
    }

    /**
     * Get isConfirmedPhone
     *
     * @return boolean
     */
    public function getIsConfirmedPhone()
    {
        return $this->isConfirmedPhone;
    }
}
