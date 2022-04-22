<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="details")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DetailsRepository")
 * @UniqueEntity(fields={"email"}, message="Email {{ value }} already exists")
 */
class Details {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", inversedBy="details")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var string
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Regex("/^[A-ZŁŚ]{1}+[a-ząęółśżźćń]+$/",message="Imię musi zaczynać się wielką literą.")
     * @Assert\Length(
     *     min=3,
     *     max=30,
     *     minMessage="Imię musi mieć minimum 3 znaki.",
     *     maxMessage="Imię nie może mieć węcej niż 30 znaków."
     * )
     * @ORM\Column(name="first_name", type="string",length=30)
     */
    protected $firstName;

    /**
     * @var string
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Regex("/^[A-ZŁŚ]{1}+[a-ząęółśżźćń]+$/", message="Nazwisko musi zaczynać się wielką literą.")
     *  * @Assert\Length(
     *     min=3,
     *     max=30,
     *     minMessage="Nazwisko musi mieć minimum 3 znaki.",
     *     maxMessage="Nazwisko nie może mieć węcej niż 30 znaków."
     * )
     * @ORM\Column(name="last_name", type="string",length=30)
     */
    protected $lastName;

    /**
     * @var string
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Regex("/^[A-ZŁŚ]{1}+[a-ząęółśżźćń]+-[0-9]{1,4}$/",message="Nazwa ulicy musi zaczynać się wielką literą. W formacie ULICA-NUMER")
     * @Assert\Length(
     *     min=3,
     *     max=30,
     *     minMessage="Nazwa ulicy musi mieć minimum 3 znaki.",
     *     maxMessage="Nazwa nie może mieć węcej niż 30 znaków."
     * )
     * @ORM\Column(type="string", length=30)
     */
    protected $street;

    /**
     * @var string
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Regex("/^[A-ZŻŹŃŁŚ]{1}+[a-ząęółśżźćń]+$/", message="Miasto musi zaczynać się wielką literą.")
     *  * @Assert\Length(
     *     min=3,
     *     max=30,
     *     minMessage="Nazwisko musi mieć minimum 3 znaki.",
     *     maxMessage="Nazwisko nie może mieć węcej niż 30 znaków."
     * )
     * @ORM\Column(type="string", length=30)
     */
    protected $city;

    /**
     * @var string
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Regex("/^[0-9]{2}-[0-9]{3}$/", message="Kod pocztowy musi mieć format 00-000")
     * @ORM\Column(type="string", name="post_code", length=6)
     *
     */
    protected $postCode;

    /**
     * @var integer
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Regex("/^\d{9}$/", message="Numer telefonu powinien mieć format 123456789")
     * @ORM\Column(name="phone_number", type="string")
     */
    protected $phoneNumber;

    /**
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Email(message="Proszę podać email w poprawnym formacie.")
     * @ORM\Column(type="string", length=254, unique=true)
     */
    private $email;


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @param string $postCode
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;
    }

    /**
     * @return int
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param int $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string
     */
    public function setEmail($email) {
        $this->email = $email;
    }

}