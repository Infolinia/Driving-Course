<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\CoursePayment;
use AppBundle\Entity\Opinion;
use AppBundle\Entity\ParticipantCourse;
use AppBundle\Entity\Ride;
use AppBundle\Entity\User;
use AppBundle\Form\CategoryType;
use AppBundle\Form\OpinionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ParticipantController extends Controller {

    /**
     * @Route("/", name="participant_index")
     * @return Response
     */
    public function indexAction() {
        return $this->render('course/index.html.twig');
    }

    /**
     * @Route("/participant/my/ride", name="participant_my_ride")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function myRideAction(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_PARTICIPANT"]);
        $entityManager = $this->getDoctrine()->getManager();
        $rides = $entityManager->getRepository(Ride::class)->findActiveRideParticipant($this->getUser());

        return $this->render(
            'participant/ride.html.twig', array('rides' => $rides)
        );

    }

    /**
     * @Route("/opinion/add/{id}/{category}/{week}",defaults={"week"=0}, name="opinion_add")
     * @param Request $request
     * @param User $user
     * @param Category $category
     * @param $week
     * @return Response
     */
    public function addAction(Request $request, User $user=null, Category $category=null, $week) {
        if ($user == null) {
            $this->addFlash("error", "Taki użytkownik nie istnieje.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if(!$user->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('my_courses'));
        }
        if ($category == null) {
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if ($week < 0) {
            $this->addFlash("error", "Week musi być większy od zera.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if ($week > 3) {
            $this->addFlash("error", "Week musi być mniejszy od trzech.");
            return $this->redirect($this->generateUrl('courses'));
        }


        $this->denyAccessUnlessGranted(["ROLE_PARTICIPANT"]);
        $form = $this->createForm(OpinionType::class, null);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        $pc = $entityManager->getRepository(ParticipantCourse::class)->findOneBy(array("user_participant"=>$this->getUser(), "user_instructor"=>$user));

        if(!$pc) {
            $this->addFlash("error", "Nie posiadasz takiego kursu.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $cp = $entityManager->getRepository(CoursePayment::class)->findOneBy(array("participantCourse" => $pc));
        if(!$cp or $cp->getStatus()=='NOT_PAID'){
            $this->addFlash("error", "Aby dodać opinię musisz opłacić kurs.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $opinion = $entityManager->getRepository(Opinion::class)->findBy(array("opinion_owner"=>$user, "opinion_participant"=>$this->getUser()));


        if($opinion){
            $this->addFlash("error", "Dodałeś już opinię dla tego instruktora.");
            return $this->redirect($this->generateUrl('ride_index', ["id"=>$user->getId(), "category"=>$category->getId(), "participant_course"=>$pc->getId(), "week"=>$week]));
        }

        if ($form->isSubmitted() && $form->isValid() ) {

            $opinion = new Opinion();
            $opinion->setTitle($form["title"]->getData());
            $opinion->setDescription($form["description"]->getData());
            $opinion->setOpinionOwner($user);
            $opinion->setOpinionParticipant($this->getUser());
            $opinion->setRate($form["rate"]->getData());

            $entityManager->persist($opinion);
            $entityManager->flush();
            $this->addFlash("success", "Opinia dla ".$user->getDetails()->getFirstName()." ".$user->getDetails()->getLastName()." została dodana.");
            return $this->redirect($this->generateUrl('ride_index', ["id"=>$user->getId(), "category"=>$category->getId(), "participant_course"=>$pc->getId(), "week"=>$week]));
        }
        return $this->render('participant/opinion.html.twig', array(
            'form' => $form->createView(),
            'instructor'=> $user
        ));
    }

}