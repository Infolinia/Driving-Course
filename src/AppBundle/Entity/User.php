<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Table(name="users")
 * @UniqueEntity(fields={"username"}, message="Username {{ value }} already exists")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements UserInterface, Serializable {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Length(
     *     min=3,
     *     max=30,
     *     minMessage="Login musi mieć minimum 3 znaki.",
     *     maxMessage="Login nie może mieć węcej niż 25 znaków."
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Length(
     *     min=3,
     *     max=30,
     *     minMessage="Hasło musi mieć minimum 3 znaki.",
     *     maxMessage="Hasło nie może mieć węcej niż 25 znaków."
     * )
     */
    private $password;

    /**
     * @var string
     */
    private $plainPassword;


    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $token;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Details", mappedBy="owner")
     */
    private $details;


    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Banishment", mappedBy="owner")
     */
    private $banishment;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Photo", mappedBy="owner")
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ride", mappedBy="instructor")
     */
    private $rides_instructor;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ride", mappedBy="participant")
     */
    private $rides_participant;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ParticipantCourse", mappedBy="user_instructor")
     */
    private $user_course;



    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ParticipantCourse", mappedBy="user_participant")
     */
    private $user_course_participant;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Opinion", mappedBy="opinion_owner")
     */
    private $opinion;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Opinion", mappedBy="opinion_participant")
     */
    private $opinion_participant;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\InstructorCategory", mappedBy="user")
     */
    private $instructor_category;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\InstructorCourse", mappedBy="user")
     */
    private $instructor_user;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Holiday", mappedBy="owner")
     */
    private $holiday;


    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @param $role
     */
    public function setRole($role){
        $this->roles = array($role);
    }

    public function __construct()
    {
        $this->roles = array("ROLE_PARTICIPANT");
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getSalt() {
        return null;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }


    public function eraseCredentials() {
        $this->plainPassword = null;
    }

    /** @see \Serializable::serialize() */
    public function serialize() {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /** @see \Serializable::unserialize()
     * @param $serialized
     */
    public function unserialize($serialized) {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @param string
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainPassword() {
        return $this->plainPassword;
    }

    /**
     * @param string
     */
    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @param string
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * @param $role
     * @return $this
     */
    public function addRole($role)
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param $role
     * @return $this
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * @return Details
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param Details $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getRidesInstructor()
    {
        return $this->rides_instructor;
    }

    /**
     * @param mixed $rides_instructor
     */
    public function setRidesInstructor($rides_instructor)
    {
        $this->rides_instructor = $rides_instructor;
    }

    /**
     * @return mixed
     */
    public function getRidesParticipant()
    {
        return $this->rides_participant;
    }

    /**
     * @param mixed $rides_participant
     */
    public function setRidesParticipant($rides_participant)
    {
        $this->rides_participant = $rides_participant;
    }

    /**
     * @return mixed
     */
    public function getUserCourse()
    {
        return $this->user_course;
    }

    /**
     * @param mixed $user_course
     */
    public function setUserCourse($user_course)
    {
        $this->user_course = $user_course;
    }

    /**
     * @return mixed
     */
    public function getInstructorCategory()
    {
        return $this->instructor_category;
    }

    /**
     * @param mixed $instructor_category
     */
    public function setInstructorCategory($instructor_category)
    {
        $this->instructor_category = $instructor_category;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        if (in_array($role, $this->getRoles())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return mixed
     */
    public function getInstructorUser()
    {
        return $this->instructor_user;
    }

    /**
     * @param mixed $instructor_user
     */
    public function setInstructorUser($instructor_user)
    {
        $this->instructor_user = $instructor_user;
    }

    /**
     * @return mixed
     */
    public function getUserCourseParticipant()
    {
        return $this->user_course_participant;
    }

    /**
     * @param mixed $user_course_participant
     */
    public function setUserCourseParticipant($user_course_participant)
    {
        $this->user_course_participant = $user_course_participant;
    }

    /**
     * @return mixed
     */
    public function getOpinion()
    {
        return $this->opinion;
    }

    /**
     * @param mixed $opinion
     */
    public function setOpinion($opinion)
    {
        $this->opinion = $opinion;
    }

    /**
     * @return mixed
     */
    public function getHoliday()
    {
        return $this->holiday;
    }

    /**
     * @param mixed $holiday
     */
    public function setHoliday($holiday)
    {
        $this->holiday = $holiday;
    }
}