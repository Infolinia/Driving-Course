<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Holiday;
use AppBundle\Entity\Opinion;
use AppBundle\Entity\ParticipantCourse;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Ride;
use AppBundle\Entity\User;
use AppBundle\Form\HourType;
use AppBundle\Form\SearchHolidayType;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class InstructorController extends Controller {

    /**
     * @Route("/", name="instructor_index")
     * @return Response
     */
    public function indexAction() {
        return $this->render('course/index.html.twig');
    }

    /**
     * @Route("/instructor/holiday/send/", name="instructor_holiday_send")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function instructorHolidaySendAction(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_INSTRUCTOR"]);
        $form = $this->createForm(SearchHolidayType::class);
        $form->handleRequest($request);
        $start_date =  $form["start_time"]->getData();
        $finish_date =  $form["finish_time"]->getData();
        $finish_date->setTime(0,0,0);
        if($finish_date < $start_date){
            $this->addFlash("error", "Data rozpoczęcia musi być większa niż data zakończenia.");
            return $this->redirect($this->generateUrl('instructor_holiday_send'));
        }

        if($start_date < new DateTime()){
            $this->addFlash("error", "Data rozpoczęcia musi być większa niż aktualna data.");
            return $this->redirect($this->generateUrl('instructor_holiday_send'));
        }

        $entityManager = $this->getDoctrine()->getManager();
        $holidays = $entityManager->getRepository(Holiday::class)->findBy(["owner" => $this->getUser()]);
        $tmp = true;
        $begin = $start_date;
        $end = $finish_date;
        $end->setTime(0,0,0);
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval ,$end);

        foreach($daterange as $date){
            $check = $entityManager->getRepository(Holiday::class)->holidayIsExhist($this->getUser(), $date);

            if(count($check) > 0)
                $tmp = false;
        }

        if ($form->isSubmitted() && $form->isValid()) {

            if(!$tmp){
                $this->addFlash("error", "Wybrane przez Ciebie dni powtarzają się.");
                return $this->redirect($this->generateUrl('instructor_holiday_send'));
            }

            $check = $entityManager->getRepository(Holiday::class)->findBy(['owner'=>$this->getUser(), 'status'=>0]);

            if($check){
                $this->addFlash("error", "Zaczekaj na poprzednią akceptację urlopu.");
                return $this->redirect($this->generateUrl('instructor_holiday_send'));
            }

            $holiday = new Holiday();
            $holiday->setOwner($this->getUser());
            $holiday->setStartDate($start_date);
            $holiday->setFinishDate($finish_date);

            $holiday->setStatus(0);
            $entityManager->persist($holiday);
            $entityManager->flush();
            $this->addFlash("success", "Zgłoszenie urlopu zostało przyjęte, czeka na akceptację administratora.");
            return $this->redirect($this->generateUrl('instructor_holiday_send'));
        }
        return $this->render(
            'instructor/holiday_send.html.twig',array("form"=> $form->createView(), "holidays"=>$holidays));
    }

    /**
     * @Route("/instructor/show/{id}", name="instructor_show")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function instructorsShowAction(Request $request, User $user = null) {

        if ($user == null) {
            $this->addFlash("error", "Taki instruktor nie istnieje.");
            return $this->redirect($this->generateUrl('instructor_index'));
        }

        if(!$user->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('instructor_index'));
        }
        $entityManager = $this->getDoctrine()->getManager();
        $opinions = $entityManager->getRepository(Opinion::class)->findBy(["opinion_owner"=>$user]);

        return $this->render(
            'instructor/show.html.twig', array('user' => $user, 'opinions'=>$opinions)
        );
    }

    /**
     * @Route("/instructor/my/ride", name="instructor_my_ride")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function myRideAction(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_INSTRUCTOR"]);
        $entityManager = $this->getDoctrine()->getManager();
        $rides = $entityManager->getRepository(Ride::class)->findActiveRideInstructor($this->getUser());

        return $this->render(
            'instructor/ride.html.twig', array('rides' => $rides)
        );

    }
}