<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Table(name="participant_courses")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserCourseRepository")
 */
class ParticipantCourse {
    const PAID = false;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="user_course")
     * @ORM\JoinColumn(name="instructor_id", referencedColumnName="id")
     */
    private $user_instructor;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ride", mappedBy="participant_course")
     */
    private $ride_participant_course;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="user_course_participant")
     * @ORM\JoinColumn(name="participant_id", referencedColumnName="id")
     */
    private $user_participant;


    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CoursePayment", mappedBy="participantCourse")
     */
    private $course_payment;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Course", inversedBy="user_course")
     * @ORM\JoinColumn(name="course_id", referencedColumnName="id")
     */
    private $course;



    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="pkk", type="string")
     */
    private $pkk;


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
     * @return mixed
     */
    public function getUserInstructor()
    {
        return $this->user_instructor;
    }

    /**
     * @param mixed $user_instructor
     */
    public function setUserInstructor($user_instructor)
    {
        $this->user_instructor = $user_instructor;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param mixed $course
     */
    public function setCourse($course)
    {
        $this->course = $course;
    }

    /**
     * @return mixed
     */
    public function getPkk()
    {
        return $this->pkk;
    }

    /**
     * @param mixed $pkk
     */
    public function setPkk($pkk)
    {
        $this->pkk = $pkk;
    }

    /**
     * @return mixed
     */
    public function getUserParticipant()
    {
        return $this->user_participant;
    }

    /**
     * @param mixed $user_participant
     */
    public function setUserParticipant($user_participant)
    {
        $this->user_participant = $user_participant;
    }

    /**
     * @return mixed
     */
    public function getCoursePayment()
    {
        return $this->course_payment;
    }

    /**
     * @param mixed $course_payment
     */
    public function setCoursePayment($course_payment)
    {
        $this->course_payment = $course_payment;
    }


}