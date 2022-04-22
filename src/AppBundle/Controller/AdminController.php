<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Category;
use AppBundle\Entity\Course;
use AppBundle\Entity\CoursePayment;
use AppBundle\Entity\Details;
use AppBundle\Entity\Holiday;
use AppBundle\Entity\InstructorCategory;
use AppBundle\Entity\InstructorCourse;
use AppBundle\Entity\News;
use AppBundle\Entity\ParticipantCourse;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Ride;
use AppBundle\Entity\User;
use AppBundle\Form\CategoryType;

use AppBundle\Form\HourType;
use AppBundle\Form\InstructorCategoryType;
use AppBundle\Form\InstructorCourseType;
use AppBundle\Form\NewsType;
use AppBundle\Form\PhotoType;
use AppBundle\Form\ProfileType;
use AppBundle\Service\EmailService;
use DateInterval;
use DatePeriod;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends Controller {

    /**
     * @Route("/", name="admin_index")
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
     * @Route("/admin/holiday/accept/", name="admin_holiday_accept")
     * @return Response
     * @throws \Exception
     */
    public function adminHolidayAcceptAction() {
        $entityManager = $this->getDoctrine()->getManager();
        $array = array();
        $instructors = $entityManager->getRepository(User::class)->findByRole("ROLE_INSTRUCTOR");
        foreach ($instructors as $key=>$value){
            $holidays = $entityManager->getRepository(Holiday::class)->findBy(["status"=>0, 'owner'=>$value]);
            foreach ($holidays as $k=>$v){
                $begin = $v->getStartDate();
                $end = $v->getFinishDate();
                $end->modify('+1 day');

                $end->setTime(0,0,0);
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($begin, $interval ,$end);
                $holidayDays = array();
                foreach($daterange as $kk=>$date){
                    $holidayDays[$kk] = $date->format("Y-m-d");
                }
                $array[$key] = array('id'=>$value->getId(), 'name'=>$value->getDetails()->getFirstName(), 'surname'=>$value->getDetails()->getLastName(), 'days'=>$holidayDays);
            }

        }
        $holidaysAccepted = $entityManager->getRepository(Holiday::class)->findBy(["status"=>1]);
        return $this->render(
            'admin/holiday/holiday_accept.html.twig', array("array"=>$array, 'holidaysAccepted'=>$holidaysAccepted));
    }


    /**
     * @Route("/admin/holiday/accept/{user}/{start_date}/{finish_date}", name="admin_holiday_accept_id")
     * @param User $user
     * @param \DateTime $start_date
     * @param \DateTime $finish_date
     * @return Response
     * @throws \Exception
     */
    public function adminHolidayAcceptIdAction(User $user, \DateTime $start_date, \DateTime $finish_date) {


        $entityManager = $this->getDoctrine()->getManager();
        $instructor = $entityManager->getRepository(User::class)->findOneBy(['id'=>$user]);

        if (!$instructor) {
            $this->addFlash("error", "Taki użytkownik nie istnieje.");
            return $this->redirect($this->generateUrl('admin_holiday_accept'));
        }
        if(!$instructor->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('admin_holiday_accept'));
        }
        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(["status"=>0, 'owner'=>$instructor->getId(),'start_date'=>$start_date, 'finish_date'=>$finish_date]);

        if (!$holiday) {
            $this->addFlash("error", "Nie ma takiej prośby do akceptacji.");
            return $this->redirect($this->generateUrl('admin_holiday_accept'));
        }


        $holiday->setStatus(1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($holiday);
        $entityManager->flush();

        $this->addFlash("success", "Zaakceptowałeś urlop instruktora.");
        $emailService = $this->get(EmailService::class);
        $emailService->sendConfirmationHolidayInstructor($user, $holiday);
        return $this->redirectToRoute("admin_holiday_accept");

    }

    /**
     * @Route("/news/add", name="news_add")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function addNewsAction(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);
        $form = $this->createForm(NewsType::class, null);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid() ) {
            $news = new News();
            $news->setTitle($form["title"]->getData());
            $news->setDescription($form["description"]->getData());
            $news->setDate(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($news);
            $entityManager->flush();
            $this->addFlash("success", "Ogłoszenie zostało dodane.");

            return $this->redirectToRoute("news_add");
        }

        return $this->render('admin/news/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/news/edit/{id}", name="news_edit")
     * @param Request $request
     * @param News|null $news
     * @return Response
     */
    public function editNewsAction(Request $request, News $news = null) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);

        if ($news == null) {
            $this->addFlash("error", "Takie ogłoszenie nie istnieje.");
            return $this->redirect($this->generateUrl('news_add'));
        }
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($news);
            $entityManager->flush();
            $this->addFlash("success", "Ogłoszenie zostało edytowane pomyślnie.");
            return $this->redirectToRoute("course_index");
        }

        return $this->render('admin/news/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/news/remove/{id}", name="news_remove")
     * @param Request $request
     * @param News|null $news
     * @return Response
     */
    public function removeNewsAction(Request $request, News $news = null) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);

        if ($news == null) {
            $this->addFlash("error", "Takie ogłoszenie nie istnieje.");
            return $this->redirect($this->generateUrl('course_index'));
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($news);
        $entityManager->flush();
        $this->addFlash("success", "Ogłoszenie zostało usunięte pomyślnie.");
        return $this->redirectToRoute("course_index");
    }

    /**
     * @Route("/courses/finished", name="courses_fnished")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function coursesFinishedAction(Request $request) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);



        $entityManager = $this->getDoctrine()->getManager();
        $courses = $entityManager->getRepository(Course::class)->findBy(array("enabled"=>1));
        $array = array();
        foreach ($courses as $key=>$c){

            $pc = $entityManager->getRepository(ParticipantCourse::class)->findBy(array("course"=>$c->getId()));
            foreach ($pc as $p) {
                $rides = $entityManager->getRepository(Ride::class)->findFinishedRide($p);
                if(count($rides) == $c->getHours()){
                    $array[$key] = array("courseCategory"=>$c->getCategory()->getCategory()->getType(), "participantFirstName"=>$p->getUserParticipant()->getDetails()->getFirstName(),"participantLastName"=>$p->getUserParticipant()->getDetails()->getLastName(), "instructorFirstName"=>$p->getUserInstructor()->getDetails()->getFirstName(),"instructorLastName"=>$p->getUserInstructor()->getDetails()->getLastName());
                }

            }
        }



        return $this->render('admin/courses/finished.html.twig', array(
            'array' => $array,
        ));
    }


    /**
     * @Route("/category/add", name="category_add")
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request) {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);
        $form = $this->createForm(CategoryType::class, null);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();
        $categories = $entityManager->getRepository(Category::class)->findAll();
        $category = $entityManager->getRepository(Category::class)->findBy(array("type"=>$form["type"]->getData()));

        if ($form->isSubmitted() && $form->isValid() ) {
            if($category){
                $this->addFlash("error", "Taka kategoria już istnieje.");
                return $this->redirectToRoute("category_add");
            }
            $category = new Category();
            $category->setType($form["type"]->getData());
            $category->setDescription($form["description"]->getData());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash("success", "Kategoria została dodana.");

            return $this->redirectToRoute("category_add");
        }

        return $this->render('admin/category/add.html.twig', array(
            'form' => $form->createView(),
            'categories' => $categories,
        ));
    }

    /**
     * @Route("/category/edit/{id}", name="category_edit")
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function editAction(Request $request, Category $category = null) {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);
        if ($category == null) {
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('category_add'));
        }
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash("success", "Kategoria została edytowana pomyślnie.");
            return $this->redirectToRoute("category_add");
        }
        return $this->render('admin/category/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/profile/show", name="profile_show")
     * @param Request $request
     * @return Response
     */
    public function profileShowIdAction(Request $request)
    {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);
        $form = $this->createFormBuilder()
            ->add('search', TextType::class, array('translation_domain' => false))
            ->add('submit', SubmitType::class, array('translation_domain' => false))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $lastName = $data["search"];
            $entityManager = $this->getDoctrine()->getManager();
            $details = $entityManager->getRepository(Details::class)->findBy(array("lastName"=>$lastName));

            if(!$details){
                $this->addFlash("error", "Nie odnaleziono użytkownika ".$lastName." w systemie.");
                return $this->redirect($this->generateUrl('profile_show'));
            }

            return $this->render('admin/profile/show.html.twig', ["details"=>$details, "form" => $form->createView()]);
        }
        return $this->render('admin/profile/show.html.twig', ["form" => $form->createView()]);

    }

    /**
     * @Route("/instructor/remove/{course}/{instructor}", name="instructor_remove")
     * @param Course $course
     * @param User $instructor
     * @return Response
     */
    public function instructorRemoveAction(Course $course = null, User $instructor = null) {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);

        if ($course == null) {
            $this->addFlash("error", "Taki kurs nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if ($instructor == null) {
            $this->addFlash("error", "Taki instruktor nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if(!$instructor->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $entityManager = $this->getDoctrine()->getManager();

        $instructorCourse = $entityManager->getRepository(InstructorCourse::class)->findOneBy(['course' => $course, 'user'=>$instructor->getId()]);

        if(!$instructorCourse){
            $this->addFlash("error", "Brak powiązania instruktora z kursem.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $entityManager->remove($instructorCourse);
        $entityManager->flush();

        $this->addFlash("success", "Instruktor  został usunięty z kursu ".$course->getDescription().".");

        return $this->redirect($this->generateUrl('course_show', ['course'=>$course->getId()]));
    }

    /**
     * @Route("/category/remove/{category}/{instructor}", name="category_remove")
     * @param Request $request
     * @param Category $category
     * @param User|null $instructor
     * @return Response
     */
    public function categoryRemoveAction(Request $request, Category $category = null, User $instructor = null) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);

        if ($category == null) {
            $this->addFlash("error", "Taka kategoria nie istnieje.");
            return $this->redirect($this->generateUrl('category_add'));
        }
        if ($instructor == null) {
            $this->addFlash("error", "Taki instruktor nie istnieje.");
            return $this->redirect($this->generateUrl('courses'));
        }

        if(!$instructor->hasRole("ROLE_INSTRUCTOR")){
            $this->addFlash("error", "Taki użytkownik nie jest instruktorem.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $entityManager = $this->getDoctrine()->getManager();

        $instructorCategory = $entityManager->getRepository(InstructorCategory::class)->findOneBy(['category' => $category, 'user'=>$instructor->getId()]);

        if(!$instructorCategory){
            $this->addFlash("error", "Brak powiązania instruktora z kategorią.");
            return $this->redirect($this->generateUrl('courses'));
        }

        $entityManager->remove($instructorCategory);
        $entityManager->flush();
        $this->addFlash("success", "Kategoria ".$category->getDescription()." została usunięta od instruktora.");

        return $this->redirect($this->generateUrl('profile_edit', ['id'=>$instructor->getId()]));
    }

    /**
     * @Route("/alerts/show", name="alerts_show")
     * @return Response
     */
    public function alertsShowAction()
    {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);
        $entityManager = $this->getDoctrine()->getManager();
        $instructors = $entityManager->getRepository(User::class)->findByRole("ROLE_INSTRUCTOR");
        $courses = $entityManager->getRepository(Course::class)->findAll();
        return $this->render('admin/alerts/show.html.twig', ["instructors"=>$instructors, "courses"=>$courses]);
    }

    /**
     * @Route("/instructor/hour/show", name="instructor_hour_show")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function instructorHourShowAction(Request $request)
    {
        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);

        $form = $this->createForm(HourType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $start_time =  $form["start_time"]->getData();

            $finish_time = $form["finish_time"]->getData();
            $dtt = new \DateTime();

            if($finish_time->format("Y-m-d H:i") > $dtt->format("Y-m-d H:i")){
                $this->addFlash("error", "Data zakończenia nie może być wieksza niż data dzisiejsza.");
                return $this->redirect($this->generateUrl('instructor_hour_show'));
            }

            $instructors = $entityManager->getRepository(User::class)->findByRole("ROLE_INSTRUCTOR");


                $array = array();
                foreach ($instructors as $key => $instructor) {
                    $rides = $entityManager->getRepository(Ride::class)->findFinished($instructor, $start_time, $finish_time);
                    $array[$key][] = array("name" => $instructor->getDetails()->getFirstName(), 'surname' => $instructor->getDetails()->getLastName(), 'hours' => count($rides));

                }
                return $this->render('admin/instructor/hour.html.twig', ['form' => $form->createView(), 'array' => $array]);

        }
        return $this->render('admin/instructor/hour.html.twig', [ 'form' => $form->createView()]);

    }

    /**
     * @Route("/profile/edit/{id}", name="profile_edit")
     * @param Request $request
     * @param User|null $user
     * @return Response
     */
    public function editProfileAction(Request $request, User $user = null) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN"]);
        if ($user == null) {
            $this->addFlash("error", "Taki użytkownik nie istnieje.");
            return $this->redirect($this->generateUrl('profile_show'));
        }
        $photo = new Photo();
        $photoForm = $this->createForm(PhotoType::class, $photo);
        $photoForm->handleRequest($request);


        $form = $this->createForm(ProfileType::class, array('details'=>$user->getDetails()), array('roles'=>$user->getRoles()[0],'show'=>1));
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();
        $detail = $entityManager->getRepository(Details::class)->findOneBy(['owner' => $user->getId()]);


        $categories = $entityManager->getRepository(Category::class)->findAll();



        $choices2 = [];

        foreach ($user->getDetails()->getOwner()->getInstructorCategory() as $c) {
            if(!in_array($c->getCategory()->getType(),  $choices2)){
                array_push($choices2, $c->getCategory()->getType());
            }
        }

        $choices = [];
        foreach ($categories as $choice) {
            if(!in_array($choice->getType(),  $choices2)) {
                $choices[$choice->getType()] = $choice->getType();
            }
        }

        $categoryForm = $this->createForm(InstructorCategoryType::class, null, array('categories'=>$choices));
        $categoryForm->handleRequest($request);

        if ($photoForm->isSubmitted() && $photoForm->isValid()) {
            /** @var UploadedFile */
            $file = $photo->getImage();
            $fileName = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('photos_directory'),
                    $fileName
                );
                $entityManager = $this->getDoctrine()->getManager();
                $p = $entityManager->getRepository(Photo::class)->findOneBy(["owner" => $user->getId()]);

                if ($p != null) {
                    $p->setImage($fileName);
                    $entityManager->persist($p);
                } else {
                    $photo->setOwner($user);
                    $photo->setImage($fileName);
                    $entityManager->persist($photo);
                }
                $photo->setImage($fileName);
                $entityManager->flush();
                $this->addFlash("success", "Zdjecie profilowe zostało dodane.");

            } catch (FileException $e) {
                $this->addFlash("error", "Błąd dodawania zdjęcia: " . $e);
            }
            return $this->redirectToRoute("profile_edit", ['id' => $user->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid() ) {
            $entityManager = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $role = $data["roles"];
            $user->setRole($role);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash("success", "Dane użytkownika ".$user->getDetails()->getLastName()." zostały zmienione pomyślnie.");
            return $this->redirectToRoute("profile_edit", ['id' => $user->getId()]);
        }

        if ($categoryForm->isSubmitted() && $categoryForm->isValid() ) {
            $entityManager = $this->getDoctrine()->getManager();
            $categories = $categoryForm["categories"]->getData();
            $c = $entityManager->getRepository(Category::class)->findOneBy(['type' => $categories]);

            $instructorCategory = new InstructorCategory();
            $instructorCategory->setCategory($c);
            $instructorCategory->setUser($user);

            $entityManager->persist($instructorCategory);
            $entityManager->flush();

            $this->addFlash("success", "Kategoria " . $categories . " została dodana intruktorowi " . $user->getDetails()->getFirstName() . " " . $user->getDetails()->getLastName() . ".");

            return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]));
        }

        if($user->hasRole("ROLE_INSTRUCTOR")) {
            return $this->render('admin/profile/edit.html.twig', array(
                'form' => $form->createView(),
                'categoryForm' => $categoryForm->createView(),
                "detail"=>$detail,
                "categories"=>$categories,
                "photoForm"=>$photoForm->createView(),
            ));
        }
        return $this->render('admin/profile/edit.html.twig', array(
            'form' => $form->createView(),
            "detail"=>$detail,
            "categories"=>$categories,
        ));
    }

}