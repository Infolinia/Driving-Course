<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(name="description", type="text")
     */
    protected $description;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CourseCategory", mappedBy="category")
     */
    private $course;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ride", mappedBy="category")
     */
    private $ride;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\InstructorCategory", mappedBy="category")
     */

    private $instructor_category;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return mixed
     */
    public function getRide()
    {
        return $this->ride;
    }

    /**
     * @param mixed $ride
     */
    public function setRide($ride)
    {
        $this->ride = $ride;
    }


}