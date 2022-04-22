<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="course_payment")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserCourseRepository")
 */
class CoursePayment {

    const NOTPAID = 'NOT_PAID';
    const PAID = 'PAID';


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
     * @var string
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    protected $token;


    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ParticipantCourse", inversedBy="course_payment")
     * @ORM\JoinColumn(name="participant_course_id", referencedColumnName="id")
     */
    private $participantCourse;





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
    public function getCourse()
    {
        return $this->participantCourse;
    }

    /**
     * @param $participantCourse
     */
    public function setCourse($participantCourse)
    {
        $this->participantCourse = $participantCourse;
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
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getParticipantCourse()
    {
        return $this->participantCourse;
    }

    /**
     * @param mixed $participantCourse
     */
    public function setParticipantCourse($participantCourse)
    {
        $this->participantCourse = $participantCourse;
    }


}