<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="rides")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RideRepository")
 */
class Ride {

    const STATUS_FREE = "free";
    const STATUS_BUSY = "busy";
    const STATUS_FINISHED = "finished";
    const STATUS_HOLIDAY = "holiday";
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="status", type="string")
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category", inversedBy="ride")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", unique=false, nullable=true)
     */
    protected $category;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_time", type="datetime")
     */
    protected $dateTime;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="rides_instructor")
     * @ORM\JoinColumn(name="instructor_id", referencedColumnName="id", nullable=true)
     */
    private $instructor;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="rides_participant")
     * @ORM\JoinColumn(name="participant_id", referencedColumnName="id", nullable=true)
     */
    private $participant;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ParticipantCourse", inversedBy="ride_participant_course")
     * @ORM\JoinColumn(name="participantCourse_id", referencedColumnName="id", nullable=true)
     */
    private $participant_course;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTime $dateTime
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return mixed
     */
    public function getInstructor()
    {
        return $this->instructor;
    }

    /**
     * @param mixed $instructor
     */
    public function setInstructor($instructor)
    {
        $this->instructor = $instructor;
    }

    /**
     * @return mixed
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @param mixed $participant
     */
    public function setParticipant($participant)
    {
        $this->participant = $participant;
    }

    /**
     * @return mixed
     */
    public function getParticipantCourse()
    {
        return $this->participant_course;
    }

    /**
     * @param mixed $participant_course
     */
    public function setParticipantCourse($participant_course)
    {
        $this->participant_course = $participant_course;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }


}