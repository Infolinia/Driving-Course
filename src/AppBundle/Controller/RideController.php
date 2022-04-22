<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Banishment;
use AppBundle\Entity\Category;
use AppBundle\Entity\CourseCategory;
use AppBundle\Entity\CoursePayment;
use AppBundle\Entity\Holiday;
use AppBundle\Entity\Opinion;
use AppBundle\Entity\ParticipantCourse;
use AppBundle\Entity\Ride;
use AppBundle\Entity\User;
use AppBundle\Service\EmailService;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class RideController extends Controller {

    /**
     * @Route("/ride/{id}/{category}/{participant_course}/{week}", defaults={"week"=0}, name="ride_index")
     * @param User $user
     * @param Category $category
     * @param ParticipantCourse $participant_course
     * @param null $week
     * @return Response
     * @throws Exception
     */
    public function indexAction(User $user = null, Category $category = null, ParticipantCourse $participant_course = null, $week = null) {


        $this->denyAccessUnlessGranted(["ROLE_PARTICIPANT"]);

        if($category == null){
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if ($user == null) {
            $this->addFlash("error", "Taki instruktor nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if(!$user->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if ($participant_course == null) {
            $this->addFlash("error", "Taki kurs nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if ($week < 0) {
            $this->addFlash("error", "Week musi być większy od zera.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if ($week > 3) {
            $this->addFlash("error", "Week musi być mniejszy od trzech.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $entityManager = $this->getDoctrine()->getManager();



        $cp = $entityManager->getRepository(CoursePayment::class)->findOneBy(array("participantCourse" => $participant_course));
        if(!$cp or $cp->getStatus()=='NOT_PAID'){
            $this->addFlash("error", "Ten kurs nie został przez Ciebie opłacony!");
            return $this->redirect($this->generateUrl('courses'));
        }

        $pc2 = $entityManager->getRepository(ParticipantCourse::class)->findOneBy(array("user_participant"=>$this->getUser(), "user_instructor"=>$user));

        if(!$pc2) {
            $this->addFlash("error", "Nie posiadasz takiego kursu.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $cp2 = $entityManager->getRepository(CoursePayment::class)->findOneBy(array("participantCourse" => $pc2));
        if($cp2->getStatus()=='NOT_PAID'){
            $this->addFlash("error", "Aby dodać opinię musisz opłacić kurs.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $from = new DateTime();
        if($week != null) {
            $from->modify('+'.($week * 7).' day');
        }
        $period = new DatePeriod($from, new DateInterval('P1D'), 8);

        $entityManager = $this->getDoctrine()->getManager();
        $database = $entityManager->getRepository(Ride::class)->findBy(["instructor" => $user]);

        $dynamic_days = array();
        $database_days = array();

        foreach ($period as $day) {

            if(!$this->isWeekend($day->format("Y-m-d H:i")))
            $dynamic_days[] = $day;

        }

        foreach ($database as $day) {
            $database_days[] = $day->getDateTime();
        }

        $array = $this->setArray($dynamic_days, $database_days, 8, 18, $user);

        $logged = $this->getUser();

        $show = true;
        $opinion = $entityManager->getRepository(Opinion::class)->findBy(array("opinion_owner"=>$user, "opinion_participant"=>$logged));

        if($opinion){
            $show = false;
        }
        return $this->render('ride/index.html.twig', ['category'=>$category,'show'=>$show, 'week'=>$week, 'data'=>$array, 'logged' => $logged, 'instructor'=>$user, 'participant_course'=>$participant_course]);
    }

    function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }


    /**
     * @Route("/ride/reserve/{id}/{category}/{day}/{hour}/{week}", defaults={"week"=0}, name="ride_reserve")
     * @param User $user
     * @param Category|null $category
     * @param DateTime $day
     * @param DateTime $hour
     * @param $week
     * @return Response
     * @throws Exception
     */
    public function reserveAction(User $user = null, Category $category = null, DateTime $day, DateTime $hour, $week) {

        $this->denyAccessUnlessGranted(["ROLE_PARTICIPANT"]);

        $hourAvalible = array('06:00', '08:00', '10:00','12:00','14:00','16:00','18:00');

        if(!in_array($hour->format("H:i"),$hourAvalible)){
            $this->addFlash("error", "Wprowadzono niepoprawną godzinę.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if ($user == null) {
            $this->addFlash("error", "Taki instruktor nie istnieje.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if(!$user->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if($category == null){
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $entityManager = $this->getDoctrine()->getManager();

        $holidays = $entityManager->getRepository(Holiday::class)->findBy(["status" => 1, 'owner' => $user]);
        $arrayTmp = array();
        $arrayTmp = $this->isHoliday($holidays);
        $dt =   $day->setTime($hour->format("H"),$hour->format("i"),$hour->format("s"));
        if (in_array($day->format("Y-m-d"), $arrayTmp)) {
            $this->addFlash("error", "W tym dniu instruktor ma urlop.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $pc = $entityManager->getRepository(ParticipantCourse::class)->findOneBy(["user_instructor"=>$user,"user_participant"=>$this->getUser()]);

        if(!$this->isAvailable($dt) or $this->isWeekend($day->format("Y-m-d"))) {
            $this->addFlash("error", "Na wybrany termin nie można się zarejestrować.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if(!$pc) {
            $this->addFlash("error", "Nie posiadasz takiego kursu.");
            return $this->redirect($this->generateUrl('my_courses'));
        }


        if ($pc->getCoursePayment()->getStatus() == 'NOT_PAID') {
            $this->addFlash("error", "Nie opłaciłeś takiego kursu.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $ride = $entityManager->getRepository(Ride::class)->findOneBy(['instructor' => $user->getId(), 'dateTime'=>$dt]);


        if ($ride != null and $ride->getStatus() == 'canceled') {
            $this->addFlash("error", "Rezerwacja na ten termin została zablokowana.");
            return $this->redirect($this->generateUrl('my_courses'));
        }


        if ($ride != null and $ride->getStatus() == 'busy') {
            $this->addFlash("error", "Ten termin jest już zajęty.");
            return $this->redirect($this->generateUrl('my_courses'));
        }
        $i = 0;
        $i1 = 0;

        $ride1 = $entityManager->getRepository(Ride::class)->findFinishedRide($pc);
        $ride2 = $entityManager->getRepository(Ride::class)->findNotFinishedRide($pc);

        if(count($ride1) + count($ride2) >= $pc->getCourse()->getHours()){
            $this->addFlash("error", "Nie możesz dokonywać już rezerwacji. Ten kurs posiada ". $pc->getCourse()->getHours()." godzin jazd szkoleniowych.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $ride2 = $entityManager->getRepository(Ride::class)->findActiveRideParticipant($this->getUser());

        if(count($ride2) > 3){
            $this->addFlash("error", "Wykorzystałeś maksymalną liczbę rezerwacji na ten tydzień.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $ride3 = $entityManager->getRepository(Ride::class)->findDayReservation($this->getUser(), $dt);


        if(count($ride3) > 1){
            $this->addFlash("error", "Wykorzystałeś maksymalną liczbę rezerwacji na ten dzień.");
            return $this->redirect($this->generateUrl('my_courses'));
        }
        $banishment = $entityManager->getRepository(Banishment::class)->findOneBy(['owner' => $this->getUser()->getId()]);

        if($banishment){
            $this->addFlash("error", "Aby zajerestrować się kolejny raz musisz odczekać 24h.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if($ride) {
            $ride->setStatus("busy");
            $ride->setDateTime($dt);
            $ride->setInstructor($user);
            $ride->setParticipantCourse($pc);
            $ride->setParticipant($this->getUser());
            $ride->setCategory($category);
            $entityManager->persist($ride);
            $entityManager->flush();
        }else {
            $ride = new Ride();
            $ride->setStatus("busy");
            $ride->setDateTime($dt);
            $ride->setInstructor($user);
            $ride->setParticipantCourse($pc);
            $ride->setParticipant($this->getUser());
            $ride->setCategory($category);
            $entityManager->persist($ride);
            $entityManager->flush();
        }
        $emailService = $this->get(EmailService::class);
        $emailService->sendReserveEmailToParticipant($this->getUser(),$user, $dt, $category->getType());
        $emailService->sendReserveEmailToInstructor($user, $this->getUser(), $dt, $category->getType());
        $this->addFlash("success", "Pomyślnie zarezerwowałeś termin: ". $day->format("Y-m-d H:i")." (kat. ".$category->getType().")");

        return $this->redirect($this->generateUrl('ride_index', ["id"=>$user->getId(), "category"=>$category->getId(), "participant_course"=>$ride->getParticipantCourse()->getId(),'week'=>$week]));
    }

    /**
     * @Route("/ride/instructor/cancel/{id}/{category}/{day}/{hour}", name="ride_instructor_cancel")
     * @param User $user
     * @param Category|null $category
     * @param DateTime $day
     * @param DateTime $hour
     * @return Response
     * @throws Exception
     */
    public function rideInstructorCancelAction(User $user = null, Category $category = null, DateTime $day, DateTime $hour)
    {
        $this->denyAccessUnlessGranted(["ROLE_INSTRUCTOR"]);

        $hourAvalible = array('06:00', '08:00', '10:00','12:00','14:00','16:00','18:00');

        if(!in_array($hour->format("H:i"),$hourAvalible)){
            $this->addFlash("error", "Wprowadzono niepoprawną godzinę.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if ($user == null) {
            $this->addFlash("error", "Taki kursant nie istnieje.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }

        if(!$user->hasRole("ROLE_PARTICIPANT")){
            $this->addFlash("error", "Taki użytkownik nie jest kursantem.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }

        if($category == null){
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }
        $dt =   $day->setTime($hour->format("H"),$hour->format("i"),$hour->format("s"));

        $entityManager = $this->getDoctrine()->getManager();

        $pc = $entityManager->getRepository(ParticipantCourse::class)->findOneBy(["user_instructor"=>$this->getUser()->getId(),"user_participant"=> $user->getId()]);
        if(!$pc) {
            $this->addFlash("error", "Nie posiadasz takiego kursu.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }
        $ride = $entityManager->getRepository(Ride::class)->findOneBy(['instructor' => $this->getUser()->getId(), 'participant' => $user->getId(), 'dateTime'=>$dt,'category'=>$category->getId()]);

        if(!$ride){
            $this->addFlash("error", "Nie masz godziny szkoleniowej do odwołania.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }

        if( $this->getUser()->getId() != $pc->getUserInstructor()->getId()) {
            $this->addFlash("error", "Nie masz uprawnień aby odwołać jazdę.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }
        $ride->setStatus("canceled");
        $entityManager->persist($ride);
        $entityManager->flush();
        $this->addFlash("success", "Jazda na kategorię ".$category->getType()." w ". $day->format("Y-m-d H:i")." została odwołana.");

        return $this->redirect($this->generateUrl('instructor_my_ride'));
    }

    /**
     * @Route("/ride/cancel/cancellation/{category}/{day}/{hour}", name="ride_cancel_cancellation")
     * @param Category|null $category
     * @param DateTime $day
     * @param DateTime $hour
     * @return RedirectResponse
     */
    public function cancelCancellationAction(Category $category = null, DateTime $day, DateTime $hour){
        $this->denyAccessUnlessGranted(["ROLE_INSTRUCTOR"]);

        $hourAvalible = array('06:00', '08:00', '10:00','12:00','14:00','16:00','18:00');

        if(!in_array($hour->format("H:i"),$hourAvalible)){
            $this->addFlash("error", "Wprowadzono niepoprawną godzinę.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if($category == null){
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }

        $entityManager = $this->getDoctrine()->getManager();
        $dt =   $day->setTime($hour->format("H"),$hour->format("i"),$hour->format("s"));
        $ride = $entityManager->getRepository(Ride::class)->findOneBy(['instructor' => $this->getUser()->getId(), 'dateTime'=>$dt, 'category'=>$category->getId()]);

        if(!$ride){
            $this->addFlash("error", "Nie masz godziny szkoleniowej do anulowania odwołania.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }
        if( $this->getUser()->getId() != $ride->getInstructor()->getId()) {
            $this->addFlash("error", "Nie masz uprawnień aby anulować odwołanie jazdy.");
            return $this->redirect($this->generateUrl('instructor_my_ride'));
        }

        $ride->setStatus("busy");
        $entityManager->persist($ride);

        $entityManager->flush();

        $this->addFlash("success", "Odwołanie jazdy na katgorię ".$category->getType()." w ". $dt->format("Y-m-d H:i")." zostało anulowane!");


        return $this->redirect($this->generateUrl('instructor_my_ride'));

    }

    /**
     * @Route("/ride/cancel/{id}/{category}/{day}/{hour}/{week}", defaults={"week"=0}, name="ride_cancel")
     * @param User $user
     * @param Category|null $category
     * @param DateTime $day
     * @param DateTime $hour
     * @param $week
     * @return Response
     * @throws Exception
     */
    public function cancelAction(User $user = null, Category $category = null, DateTime $day, DateTime $hour, $week)
    {

        $this->denyAccessUnlessGranted(["ROLE_PARTICIPANT"]);

        $hourAvalible = array('06:00', '08:00', '10:00','12:00','14:00','16:00','18:00');

        if(!in_array($hour->format("H:i"),$hourAvalible)){
            $this->addFlash("error", "Wprowadzono niepoprawną godzinę.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if ($user == null) {
            $this->addFlash("error", "Taki instruktor nie istnieje.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if($category == null){
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if(!$user->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('my_courses'));
        }


        $entityManager = $this->getDoctrine()->getManager();
        $pc = $entityManager->getRepository(ParticipantCourse::class)->findOneBy(["user_instructor"=>$user,"user_participant"=>$this->getUser()]);

        if(!$pc) {
            $this->addFlash("error", "Nie posiadasz takiego kursu.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if( $this->getUser()->getId() != $pc->getUserParticipant()->getId()) {
            $this->addFlash("error", "Nie masz uprawnień aby odwołać jazdę.");
            return $this->redirect($this->generateUrl('my_courses'));
        }


        if ($pc->getCoursePayment()->getStatus() == 'NOT_PAID') {
            $this->addFlash("error", "Nie opłaciłeś takiego kursu.");
            return $this->redirect($this->generateUrl('my_courses'));
        }


        $dt =   $day->setTime($hour->format("H"),$hour->format("i"),$hour->format("s"));
        $ride = $entityManager->getRepository(Ride::class)->findOneBy(['instructor' => $user->getId(), 'participant' => $this->getUser()->getId(), 'dateTime'=>$dt]);

        if(!$ride){
            $this->addFlash("error", "Nie masz godziny szkoleniowej do odwołania.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        $banishment = $entityManager->getRepository(Banishment::class)->findOneBy(['owner' => $this->getUser()->getId()]);
        $datetime = new DateTime();
        $datetime->modify('+1 day');
        if($banishment){
            $banishment->setOwner($this->getUser());
            $banishment->setFinishTime($datetime);
            $banishment->setReason(Banishment::CANCEL);
            $entityManager->persist($banishment);
            $entityManager->flush();
        }else{
            $b = new Banishment();
            $b->setOwner($this->getUser());
            $b->setFinishTime($datetime);
            $b->setReason(Banishment::CANCEL);
            $entityManager->persist($b);
            $entityManager->flush();
        }
        $ride->setStatus("free");
        $entityManager->persist($ride);
        $entityManager->flush();
        $emailService = $this->get(EmailService::class);
        $emailService->sendReserveCancelEmailToParticipant($this->getUser(),$user, $dt, $category->getType());
        $emailService->sendReserveCancelEmailToInstructor($user, $this->getUser(), $dt, $category->getType());


        $this->addFlash("success", "Jazda na katgorię ".$category->getType()." w ". $dt->format("Y-m-d H:i")." została odwołana.");

        return $this->redirect($this->generateUrl('ride_index', ["category"=>$category->getId(), "id"=>$user->getId(), "participant_course"=>$pc->getId(), 'week'=>$week]));
    }




    public function isAvailable($day){
        $today = new DateTime();
        $max = new DateTime();
        $max->modify("+30 day");
        if ($day > $today && $day < $max){
            return true;
        }
        return false;
    }

    public function isFree($value, $days){
        foreach ($days as $day) {
            if($value->format("Y-m-d H:i") === $day->format("Y-m-d H:i"))
                return false;
        }
        return true;
    }

    public function setArray($dynamic_days, $database_days, $start_hour, $stop_hour, $user){
        $array = array();
        $today = new DateTime();
        $entityManager = $this->getDoctrine()->getManager();
        $holidays = $entityManager->getRepository(Holiday::class)->findBy(["status" => 1, 'owner' => $user]);
        $arrayTmp = array();
        $arrayTmp = $this->isHoliday($holidays);
        $tablica = array('Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota');
       // echo $dni[date('w')];
        for($i = 0; $i <= $stop_hour - $start_hour; $i+=2) {
            foreach ($dynamic_days as $day) {
                if($i == 0)
                    $array[0][] = $day->format("Y-m-d").' '.$tablica[$day->format("w")];

                $day->setTime($start_hour+$i, 0, 0);
                $array[1+$i][0] = $day->format("H:i:s");




                    if (in_array($day->format("Y-m-d"), $arrayTmp)) {

                        $array[1 + $i][] = array("holiday" => array('date' => $day->format("Y-m-d")));
                    }
                 else if ($this->isFree($day, $database_days)) {
                    if ($today < $day){
                        $array[1 + $i][] = array("free"=>array('date'=>$day->format("Y-m-d H:i")));
                    }else {
                        $array[1 + $i][] = "empty";
                    }
                } else {
                    if ($today < $day){
                        $entityManager = $this->getDoctrine()->getManager();
                        $dt = new DateTime();
                        $dt->setDate($day->format("Y"),$day->format("m"),$day->format("d"));
                        $dt->setTime($day->format("H"),$day->format("i"),$day->format("s"));
                        $ride = $entityManager->getRepository(Ride::class)->findOneBy(['instructor' => $user, 'dateTime'=>$dt]);


                        if($ride) {
                            if ($ride->getStatus() == 'free') {
                                $array[1 + $i][] = array("free" => array('date' => $day->format("Y-m-d H:i"), 'owner' => $ride->getParticipant()->getId(), 'category' => $ride->getCategory()));
                            } elseif ($ride->getStatus() == 'canceled') {
                                $array[1 + $i][] = array("canceled" => array('date' => $day->format("Y-m-d H:i"), 'owner' => $ride->getParticipant()->getId(), 'category' => $ride->getCategory()));
                            } elseif ($ride->getStatus() == 'busy') {
                                $array[1 + $i][] = array("busy" => array('date' => $day->format("Y-m-d H:i"), 'owner' => $ride->getParticipant()->getId(), 'category' => $ride->getCategory()));
                            }
                        }
                        else{
                            $array[1 + $i][] = array("busy" => array('date' => $day->format("Y-m-d H:i"), 'owner' => -1, 'category' => -1));
                        }
                    }else {
                        $array[1 + $i][] = "finished";
                    }
                }
            }
        }

        return $array;
    }

    public function isHoliday($holidays)
    {
        $array = array();
        foreach ($holidays as $v) {
            $begin = $v->getStartDate();
            $end = $v->getFinishDate();
            $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod($begin, $interval, $end);
            foreach ($daterange as $date) {
                array_push($array, $date->format("Y-m-d"));
            }

        }



       return $array;

    }


}