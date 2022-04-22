<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Table(name="opinions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OpinionRepository")
 */
class Opinion
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Length(
     *     min=5,
     *     max=70,
     *     minMessage="Tytuł opinii musi mieć minimum 5 znaków.",
     *     maxMessage="Tytuł opinii nie może mieć węcej niż 70 znaków."
     * )
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 1,
     *      max = 6,
     *      minMessage = "Minimalna ocena dla instrukto to 1.",
     *      maxMessage = "Maksymalna ocena dla instruktora to 6."
     * )
     */
    protected $rate;

    /**
     * @var string
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank(message="Proszę uzupełnić dane.")
     * @Assert\Length(
     *     min=30,
     *     max=300,
     *     minMessage="Opis Opinii musi mieć minimum 20 znaków.",
     *     maxMessage="Opis Opinii nie może mieć węcej niż 300 znaków."
     * )
     */
    protected $description;



    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="opinion")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $opinion_owner;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="opinion_participant")
     * @ORM\JoinColumn(name="participant_id", referencedColumnName="id")
     */
    private $opinion_participant;


    /**
     * @return mixed
     */
    public function getOpinionOwner()
    {
        return $this->opinion_owner;
    }

    /**
     * @param mixed $opinion_owner
     */
    public function setOpinionOwner($opinion_owner)
    {
        $this->opinion_owner = $opinion_owner;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
    public function getOpinionParticipant()
    {
        return $this->opinion_participant;
    }

    /**
     * @param $opinion_participant
     */
    public function setOpinionParticipant($opinion_participant)
    {
        $this->opinion_participant = $opinion_participant;
    }

    /**
     * @return string
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param string $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

}


