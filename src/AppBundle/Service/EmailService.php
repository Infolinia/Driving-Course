<?php

namespace AppBundle\Service;

use AppBundle\Entity\Holiday;
use AppBundle\Entity\User;
use AppBundle\Model\Contact;
use Swift_Message;

class EmailService {

    protected $mailer;
    protected $twig;
    protected $adminEmail;
    protected $adminName;

    /**
     * EmailService constructor.
     * @param \Swift_Mailer $mailer
     * @param $twig
     * @param $adminEmail
     * @param $adminName
     */
    public function __construct(\Swift_Mailer $mailer, $twig, $adminEmail, $adminName) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->adminEmail = $adminEmail;
        $this->adminName = $adminName;
    }

    /**
     * @param $token
     * @param User $user
     */
    public function sendResetPasswordTokenEmail($token, User $user) {
        $message = (new Swift_Message('Resetowanie hasła!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($user->getDetails()->getEmail())
            ->setBody($this->twig->render('email/resetting.html.twig',  array('token' => $token)), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param $newPassword
     * @param User $user
     */
    public function sendNewPasswordEmail($newPassword, User $user) {
        $message = (new Swift_Message('Nowe hasło!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($user->getDetails()->getEmail())
            ->setBody($this->twig->render('email/new.html.twig',  array('newPassword' => $newPassword)), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param User $user
     */
    public function sendRegisterEmail(User $user) {
        $message = (new Swift_Message('Konto utworzone!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($user->getDetails()->getEmail())
            ->setBody($this->twig->render('email/register.html.twig',
                array('login' => $user->getUsername())), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param Contact $contact
     */
    public function sendContactEmail(Contact $contact) {
        $message = (new Swift_Message('Wiadomość wysłana!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($this->adminEmail)
            ->setBody($this->twig->render('email/contact.html.twig',  array('name' => $contact->getName(), 'email' => $contact->getEmail(), 'subject' => $contact->getSubject(), 'message' => $contact->getMessage())), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param Contact $contact
     */
    public function sendConfirmContactEmail(Contact $contact) {
        $message = (new Swift_Message('Potwierdzenie wysłania wiadomości!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($contact->getEmail())
            ->setBody($this->twig->render('email/confirm.html.twig',  array('name' => $contact->getName(), 'email' => $contact->getEmail(), 'subject' => $contact->getSubject(), 'message' => $contact->getMessage())), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param User $participant
     * @param User $instructor
     * @param \DateTime $dateTime
     * @param $category
     */
    public function sendReserveEmailToParticipant(User $participant, User $instructor, \DateTime $dateTime, $category) {
        $message = (new Swift_Message('Rezerwacja utworzona!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($participant->getDetails()->getEmail())
            ->setBody($this->twig->render('email/reserve_participant.html.twig',  array('datetime' => $dateTime, 'category'=> $category, 'instructor'=>$instructor)), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param User $participant
     * @param User $instructor
     * @param \DateTime $dateTime
     * @param $category
     */
    public function sendReserveEmailToInstructor(User $instructor, User $participant, \DateTime $dateTime, $category) {
        $message = (new Swift_Message('Rezerwacja utworzona!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($instructor->getDetails()->getEmail())
            ->setBody($this->twig->render('email/reserve_instructor.html.twig',  array('datetime' => $dateTime, 'category'=> $category, 'participant'=>$participant)), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param User $participant
     * @param User $instructor
     * @param \DateTime $dateTime
     * @param $category
     */
    public function sendReserveCancelEmailToParticipant(User $participant, User $instructor, \DateTime $dateTime, $category) {
        $message = (new Swift_Message('Rezerwacja anulowana!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($participant->getDetails()->getEmail())
            ->setBody($this->twig->render('email/reserve_participant_cancel.html.twig',  array('datetime' => $dateTime, 'category'=> $category, 'instructor'=>$instructor)), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param User $participant
     * @param User $instructor
     * @param \DateTime $dateTime
     * @param $category
     */
    public function sendReserveCancelEmailToInstructor(User $instructor, User $participant, \DateTime $dateTime, $category) {
        $message = (new Swift_Message('Rezerwacja anulowana!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($instructor->getDetails()->getEmail())
            ->setBody($this->twig->render('email/reserve_instructor_cancel.html.twig',  array('datetime' => $dateTime, 'category'=> $category, 'participant'=>$participant)), 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param User $instructor
     * @param Holiday $holiday
     */
    public function sendConfirmationHolidayInstructor(User $instructor, Holiday $holiday) {
         $startDate = $holiday->getStartDate();
        $finishDate = $holiday->getFinishDate();
        $message = (new Swift_Message('Akceptacja urlopu!'))
            ->setFrom(array($this->adminEmail => $this->adminName))
            ->setTo($instructor->getDetails()->getEmail())
            ->setBody($this->twig->render('email/holiday_accept.html.twig',  array('startDate' => $startDate, 'finishDate'=> $finishDate)), 'text/html');
        $this->mailer->send($message);

    }

}