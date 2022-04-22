<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="holidays")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HolidayRepository")
 */
class Holiday {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime")
     */
    protected $start_date;

    /**
     * @var \DateTime
     * @ORM\Column(name="finish_date", type="datetime")
     */
    protected $finish_date;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="holiday")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var string
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param \DateTime $start_date
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return \DateTime
     */
    public function getFinishDate()
    {
        return $this->finish_date;
    }

    /**
     * @param \DateTime $finish_date
     */
    public function setFinishDate($finish_date)
    {
        $this->finish_date = $finish_date;
    }

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

}