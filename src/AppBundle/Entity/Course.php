<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="courses")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CourseRepository")
 */
class Course {
    const ENALBED = 1;
    const DISABLED = 0;
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @var DateTime
     * @ORM\Column(name="start_time", type="datetime")
     */
    protected $startTime;

    /**
     * @var DateTime
     * @ORM\Column(name="finish_time", type="datetime")
     */
    protected $finishTime;

    /**
     * @var Integer
     * @ORM\Column(name="price", type="integer")
     */
    protected $price;

    /**
     * @var Integer
     * @ORM\Column(name="max_participants", type="integer")
     */
    protected $maxParticipants;

    /**
     * @var Integer
     * @ORM\Column(name="hours", type="integer")
     */
    protected $hours;

    /**
     * @var Integer
     * @ORM\Column(name="enabled", type="integer")
     */
    protected $enabled;



    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ParticipantCourse", mappedBy="course")
     */
    private $user_course;


    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CourseCategory", mappedBy="course")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\InstructorCourse", mappedBy="course")
     */
    private $instructor_course;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param DateTime $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return DateTime
     */
    public function getFinishTime()
    {
        return $this->finishTime;
    }

    /**
     * @param DateTime $finishTime
     */
    public function setFinishTime($finishTime)
    {
        $this->finishTime = $finishTime;
    }


    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getInstructorCourse()
    {
        return $this->instructor_course;
    }

    /**
     * @param mixed $instructor_course
     */
    public function setInstructorCourse($instructor_course)
    {
        $this->instructor_course = $instructor_course;
    }

    /**
     * @return int
     */
    public function getMaxParticipants()
    {
        return $this->maxParticipants;
    }

    /**
     * @param int $maxParticipants
     */
    public function setMaxParticipants($maxParticipants)
    {
        $this->maxParticipants = $maxParticipants;
    }

    /**
     * @return int
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * @param int $hours
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    /**
     * @return int
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param int $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }


}