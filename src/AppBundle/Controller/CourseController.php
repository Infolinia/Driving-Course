<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Course;
use AppBundle\Entity\CourseCategory;
use AppBundle\Entity\CoursePayment;
use AppBundle\Entity\InstructorCategory;
use AppBundle\Entity\InstructorCourse;
use AppBundle\Entity\News;
use AppBundle\Entity\ParticipantCourse;
use AppBundle\Entity\Ride;
use AppBundle\Entity\User;
use AppBundle\Form\CategoryType;
use AppBundle\Form\ContactType;
use AppBundle\Form\CourseType;
use AppBundle\Form\InstructorCourseType;
use AppBundle\Form\InstructorRemoveType;
use AppBundle\Model\Contact;
use AppBundle\Service\EmailService;
use DateTime;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\Cloner\Data;


class CourseController extends Controller {

    /**
     * @Route("/", name="course_index")
     * @return Response
     */
    public function indexAction() {
        $entityManager = $this->getDoctrine()->getManager();
        $news = $entityManager->getRepository(News::class)->findAll();

        return $this->render(
            'course/index.html.twig', array('news' => $news)
        );
    }

    /**
     * @Route("/contact", name="course_contact")
     * @param Request $request
     * @return Response
     */
    public function contactAction(Request $request) {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailService = $this->get(EmailService::class);
            $emailService->sendContactEmail($contact);
            $emailService->sendConfirmContactEmail($contact);
            $this->addFlash("success", "Wiadomość została wysłana do administratora.");

            return $this->redirect($this->generateUrl('course_contact'));
        }

        return $this->render(
            'course/contact.html.twig', array('form' => $form->createView())
        );
    }

    /**
     * @Route("/instructors", name="course_instructors")
     * @param Request $request
     * @return Response
     */
    public function instructorsAction(Request $request) {

        $entityManager = $this->getDoctrine()->getManager();
        $instructors = $entityManager->getRepository(User::class)->findByRole("ROLE_INSTRUCTOR");

        return $this->render(
            'course/instructors.html.twig', array('instructors' => $instructors)
        );
    }


    /**
     * @Route("/course/create", name="course_create")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function createAction(Request $request) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('course_create'))
            ->add(
                "finish_time",
                DateType::class,
                ["data" => new DateTime("+1 day"),
                    'widget' => 'single_text',
                    'html5' => true,
                ]
            )
            ->add('description', TextareaType::class,array('translation_domain' => false))
            ->add('price', IntegerType::class, array('translation_domain' => false))
            ->add('maxParticipants', IntegerType::class, array('translation_domain' => false))
            ->add('hours', IntegerType::class, array('translation_domain' => false))
            ->add('categories', EntityType::class, array(
                'class' => 'AppBundle:Category',
                'choice_label' => 'type',
            ))
            ->add('submit', SubmitType::class, array('translation_domain' => false)) ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $entityManager = $this->getDoctrine()->getManager();
            $check = $entityManager->getRepository(CourseCategory::class)->findBy(['category' => $form["categories"]->getData()->getId()]);

            if($check){
                foreach ($check as $c) {
                    if ($c->getCourse()->getEnabled() == 1) {
                        $this->addFlash("error", "Kurs z taką kategorią już istnieje.");
                        return $this->redirect($this->generateUrl('course_create'));
                    }
                }

            }

            $course = new Course();
            $course->setDescription($form["description"]->getData());
            $course->setStartTime(new DateTime());
            $course->setFinishTime($form["finish_time"]->getData());
            $course->setHours($form["hours"]->getData());
            $course->setMaxParticipants($form["maxParticipants"]->getData());
            $course->setPrice($form["price"]->getData());
            $course->setEnabled(1);
            $courseCategory = new CourseCategory();
            $courseCategory->setCourse($course);
            $courseCategory->setCategory($form["categories"]->getData());


            $entityManager->persist($course);
            $entityManager->persist($courseCategory);
            $entityManager->flush();
            $this->addFlash("success", "Kurs został utworzony.");

            return $this->redirectToRoute("course_show",["course"=>$course->getId()]);
        }
        return $this->render(
            'course/create.html.twig', array('form' => $form->createView())
        );

    }

    /**
     * @Route("/course/edit/{course}", name="course_edit")
     * @param Request $request
     * @param Course $course
     * @return Response
     */
    public function editAction(Request $request, Course $course = null) {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);

        if ($course == null) {
            $this->addFlash("error", "Taki kurs nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if ($course->getEnabled() == 0) {
            $this->addFlash("error", "Ten kurs jest już zakończony.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();
            $this->addFlash("success", "Kurs został edytowany pomyślnie.");
            return $this->redirectToRoute("course_create");
        }

        return $this->render('course/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/course/finish/{id}", name="course_finish")
     * @param Request $request
     * @param Course $course
     * @return Response
     */
    public function finishAction(Request $request, Course $course = null) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);

        if ($course == null) {
            $this->addFlash("error", "Taki kurs nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if ($course->getEnabled() == 0) {
            $this->addFlash("error", "Ten kurs jest już zakończony.");
            return $this->redirect($this->generateUrl('courses'));
        }
        $entityManager = $this->getDoctrine()->getManager();
        $course->setEnabled(0);
        $entityManager->persist($course);
        $entityManager->flush();
        $this->addFlash("success", "Kurs został zakończony pomyślnie.");
        return $this->redirectToRoute("courses");
    }

    /**
     * @Route("/courses", name="courses")
     * @param Request $request
     * @return Response
     */
    public function findAction(Request $request) {

        $entityManager = $this->getDoctrine()->getManager();
        $courses = $entityManager->getRepository(Course::class)->findBy(array(), array('id' => 'DESC'));
        $array = [];

        $i = 0;
        foreach ($courses as $c) {
            $checkParticipantCourse = $entityManager->getRepository(ParticipantCourse::class)->findBy(['course' => $c->getId()]);
            if (!$checkParticipantCourse) {
                $i = 0;
            }

            foreach ($checkParticipantCourse as $cc) {

                $check = $entityManager->getRepository(CoursePayment::class)->findOneBy(['participantCourse' => $cc->getId(), 'status' => CoursePayment::PAID]);
                if ($check) {
                    $i++;
                }


            }
            $array[$c->getId()]= array($c->getId()=>$i);
            $i = 0;
        }


        return $this->render(
            'course/courses.html.twig', array('courses' => $courses, 'array'=>$array)
        );
    }


    /**
     * @Route("/course/show/{course}", name="course_show")
     * @param Request $request
     * @param Course $course
     * @return Response
     */
    public function showAction(Request $request, Course $course = null) {
        if ($course == null) {
            $this->addFlash("error", "Taki kurs nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if($course->getEnabled() == 0){
            $this->addFlash("error", "Ten kurs został już zakończony.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $entityManager = $this->getDoctrine()->getManager();
        $instructors = $entityManager->getRepository(InstructorCategory::class)->findBy(['category' => $course->getCategory()->getCategory()->getId()]);
        $instructorCourse = $entityManager->getRepository(InstructorCourse::class)->findBy(['course' => $course->getId()]);

        $instructorList = [];
        foreach ($instructorCourse as $c) {
            $instructorList[$c->getUser()->getDetails()->getFirstName() .' '.$c->getUser()->getDetails()->getLastName()] = $c->getUser()->getId();

        }

        $choices2 = [];

        foreach ($course->getInstructorCourse() as $c) {
            if(!in_array($c->getUser()->getId(),  $choices2)){
                array_push($choices2, $c->getUser()->getId());
            }
        }

        $choices = [];
        foreach ($instructors as $choice) {
            if(!in_array($choice->getUser()->getId(),  $choices2)) {
                $choices[$choice->getUser()->getDetails()->getFirstName().' '.$choice->getUser()->getDetails()->getLastName()] = $choice->getUser()->getId();
            }
        }

        $tmp = false;
        $i = 0;
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $checkParticipantCourse = $entityManager->getRepository(ParticipantCourse::class)->findBy(['user_participant' => $this->getUser()->getId(), 'course' => $course->getId()]);

            if ($checkParticipantCourse) {
                foreach ($checkParticipantCourse as $c) {
                    $check = $entityManager->getRepository(CoursePayment::class)->findOneBy(['participantCourse' => $c->getId(), 'status' => CoursePayment::PAID]);
                    if ($check) {
                        $tmp = true;
                    }
                }

            }
        }
            $checkParticipantCourse2 = $entityManager->getRepository(ParticipantCourse::class)->findBy(['course' => $course->getId()]);

            foreach ($checkParticipantCourse2 as $c) {
                $check = $entityManager->getRepository(CoursePayment::class)->findOneBy(['participantCourse' => $c->getId(), 'status' => CoursePayment::PAID]);
                if($check){
                    $i++;
                }
            }
        if($course->getMaxParticipants() - $i <= 0){
            $this->addFlash("error", "Brak wolnych miejsc na ten kurs.");
            return $this->redirect($this->generateUrl('courses'));
        }


        if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
            $form = $this->createForm(InstructorCourseType::class, null, array('instructors'=>$choices,'instructorSelect'=>$instructorList, 'role'=>$this->getUser()->getRoles()[0], 'paid'=>$tmp));
        else
            $form = $this->createForm(InstructorCourseType::class, null, array('instructors'=>$choices,'instructorSelect'=>$instructorList));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            if($this->getUser()->hasRole("ROLE_ADMIN")) {
                $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);
                if ($course == null) {
                    $this->addFlash("error", "Taki kurs nie istnieje.");
                    return $this->redirect($this->generateUrl('courses'));
                }
                $entityManager = $this->getDoctrine()->getManager();
                $id = $form["instructors"]->getData();
                $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
                if(!$user){
                    $this->addFlash("error", "Taki użytkownik nie istnieje.");
                    return $this->redirect($this->generateUrl('courses'));
                }
                if(!$user->hasRole("ROLE_INSTRUCTOR")){
                    $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
                    return $this->redirect($this->generateUrl('instructor_my_ride'));
                }
                $instructorCourse = new InstructorCourse();
                $instructorCourse->setCourse($course);
                $instructorCourse->setUser($user);
                $entityManager->persist($instructorCourse);
                $entityManager->flush();
                $this->addFlash("success", "Instruktor " . $user->getDetails()->getFirstName() . " został przydzielony do kursu " . $course->getDescription() . ".");
            }else  if($this->getUser()->hasRole("ROLE_PARTICIPANT")) {
                $entityManager = $this->getDoctrine()->getManager();
                $id = $form["instructorSelect"]->getData();
                $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
                if(!$user){
                    $this->addFlash("error", "Taki użytkownik nie istnieje.");
                    return $this->redirect($this->generateUrl('courses'));
                }
                if(!$user->hasRole("ROLE_INSTRUCTOR")){
                    $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
                    return $this->redirect($this->generateUrl('instructor_my_ride'));
                }
                $checkParticipantCourse = $entityManager->getRepository(ParticipantCourse::class)->findOneBy(['user_instructor' => $user->getId(), 'user_participant'=>$this->getUser()->getId(), 'course'=>$course->getId()]);
                if($checkParticipantCourse) {
                    $check = $entityManager->getRepository(CoursePayment::class)->findOneBy(['participantCourse' => $checkParticipantCourse->getId(), 'status'=>CoursePayment::PAID]);
                    if($check){
                        $this->addFlash("error", "Już wcześniej opłaciłeś ten kurs.");
                        return $this->redirect($this->generateUrl('course_show', ['course'=>$course->getId(), 'paid'=>true]));

                    }
                    $checkParticipantCourse->setPkk($form["pkk"]->getData());
                    $checkParticipantCourse->setUserInstructor($user);
                    $checkParticipantCourse->setUserParticipant($this->getUser());
                    $checkParticipantCourse->setCourse($course);
                    $entityManager->persist($checkParticipantCourse);
                    $entityManager->flush();
                    return $this->redirectToRoute("payment_register", ['course' => $course->getId(), 'user' => $user->getId(), 'participant_course' => $checkParticipantCourse->getId()]);

                }
                else {
                    $userCourse = new ParticipantCourse();
                    $userCourse->setPkk($form["pkk"]->getData());
                    $userCourse->setUserInstructor($user);
                    $userCourse->setUserParticipant($this->getUser());
                    $userCourse->setCourse($course);
                    $entityManager->persist($userCourse);
                    $entityManager->flush();
                    return $this->redirectToRoute("payment_register", ['course' => $course->getId(), 'user' => $user->getId(), 'participant_course' => $userCourse->getId()]);
                }
            }
            return $this->redirect($this->generateUrl('course_show', ['course'=>$course->getId()]));
        }

        return $this->render('course/show.html.twig', array(
            'course' => $course,
            'form'=>$form->createView(),
            'paid'=> $tmp,
            'count'=>$i,
        ));
    }

    /**
     * @Route("/my/courses", name="my_courses")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function myCoursesAction(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_PARTICIPANT"]);

        $this->msleep(0.5);

        $entityManager = $this->getDoctrine()->getManager();

        $courses = $entityManager->getRepository(ParticipantCourse::class)->findBy(['user_participant'=>$this->getUser()->getId()]);
        $array = [];

        $i = 0;
        $now = new DateTime();
        foreach ($courses as $c) {
            $ride = $entityManager->getRepository(Ride::class)->findFinishedRide($c);
            if (!$ride) {
                $i = 0;
            }

            foreach ($ride as $rr) {
                $i++;
            }

            $array[$c->getCourse()->getId()]= array($c->getId()=>$i);

            $i = 0;
        }
        return $this->render(
            'course/my.html.twig', array('courses' => $courses, 'array'=>$array)
        );
    }

    public function msleep($time)
    {
        usleep($time * 1000000);
    }
}