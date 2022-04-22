<?php
/**
 * Created by PhpStorm.
 * User: Squidy
 * Date: 20.05.2018
 * Time: 13:48
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Course;
use AppBundle\Entity\CoursePayment;
use AppBundle\Entity\InstructorCategory;
use AppBundle\Entity\User;
use AppBundle\Entity\ParticipantCourse;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use SoapClient;
use SoapFault;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

class PaymentController extends Controller
{

    /**
     * @Route("/payment/test", name="payment_test")
     * @return void
     * @throws SoapFault
     */
    public function testAction()
    {

        $soap = new SoapClient("https://sandbox.przelewy24.pl/external/73622.wsdl");
        $test = $soap->TestAccess("73622", "1a05b517a34b4e5e3ac5218f15d16ad0");

        if ($test)
            $variable = 'Połączenie zostało wykonane pomyślnie.';
        else
            $variable = 'Brak dostępu.';
        echo $variable;
    }

    /**
     * @Route("/payment/cancel/{participant_course}", name="payment_cancel")
     * @param Request $request
     * @param ParticipantCourse $participant_course
     * @return RedirectResponse
     */
    public function cancelAction(Request $request, ParticipantCourse $participant_course = null)
    {

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {

            if($participant_course == null){
                $this->addFlash("error", "Nie posiadasz takiego kursu do anulowania.");
                return $this->redirect($this->generateUrl('my_courses'));
            }


            $entityManager = $this->getDoctrine()->getManager();
            if($participant_course->getCourse()->getEnabled() == 0){
                $this->addFlash("error", "Ten kurs został już zakończony.");
                return $this->redirect($this->generateUrl('my_courses'));
            }


            $check = $entityManager->getRepository(CoursePayment::class)->findOneBy(['participantCourse' => $participant_course->getId(), 'status' => CoursePayment::NOTPAID]);
            if(!$check){
                $this->addFlash("error", "Ten kurs został już przez Ciebie opłacony.");
                return $this->redirect($this->generateUrl('courses'));
            }


                $entityManager->remove($check);
                $entityManager->flush();


        }
        return $this->redirect($this->generateUrl('my_courses'));
    }

    /**
     * @Route("/payment/register/{course}/{participant_course}", name="payment_register")
     * @param Request $request
     * @param Course $course
     * @param ParticipantCourse $participant_course
     * @return Response
     * @throws SoapFault
     */
    public function registerAction(Request $request, Course $course = null, ParticipantCourse $participant_course = null)
    {

        if($course == null){
            $this->addFlash("error", "Brak takiego kursu w systemie.");
            return $this->redirect($this->generateUrl('my_courses'));
        }


        if($participant_course == null){
            $this->addFlash("error", "Nie posiadasz takiego kursu do opłacenia.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if($course->getEnabled() == 0){
            $this->addFlash("error", "Ten kurs został już zakończony.");
            return $this->redirect($this->generateUrl('my_courses'));
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $entityManager = $this->getDoctrine()->getManager();

            $check = $entityManager->getRepository(CoursePayment::class)->findBy(['participantCourse' => $participant_course->getId(), 'status' => CoursePayment::PAID]);

            if(count($check) >= $course->getMaxParticipants()){
                $this->addFlash("error", "Limit uczestników został już osiągnięty");
                return $this->redirect($this->generateUrl('my_courses'));
            }



             if($check){
            $this->addFlash("error", "Ten kurs został już przez Ciebie opłacony.");
            return $this->redirect($this->generateUrl('courses'));
            }
            $this->denyAccessUnlessGranted(["ROLE_PARTICIPANT"]);
            $price = $course->getPrice() * 100;

            $sessionId = md5(uniqid(session_id() + $course->getId(), true));
            $crc = md5($sessionId . "|73622|" . $price . "|PLN|04cd438b9c16b1c8");


            $user = $this->getUser();
            $details = array( array('name' => 'p24_session_id', 'value' => $sessionId),
                array('name' => 'p24_merchant_id', 'value' => '73622'),
                array('name' => 'p24_pos_id', 'value' => '73622'),
                array('name' => 'p24_amount', 'value' => $price),
                array('name' => 'p24_currency', 'value' => 'PLN'),
                array('name' => 'p24_description', 'value' => $course->getDescription()),
                array('name' => 'p24_client', 'value' => $user->getDetails()->getFirstName() .' 
                    '. $user->getDetails()->getLastName()),
                array('name' => 'p24_address', 'value' =>  $user->getDetails()->getStreet()),
                array('name' => 'p24_zip', 'value' => $user->getDetails()->getPostCode()),
                array('name' => 'p24_city', 'value' => $user->getDetails()->getCity()),
                array('name' => 'p24_country', 'value' => 'PL'),
                array('name' => 'p24_email', 'value' => $user->getDetails()->getEmail()),
                array('name' => 'p24_language', 'value' => 'pl'),
                array('name' => 'p24_url_status', 'value' => 'https://kurs-prawo-jazdy.pl/payment/status'),
                array('name' => 'p24_url_return', 'value' => 'https://kurs-prawo-jazdy.pl/my/courses'),
                array('name' => 'p24_api_version', 'value' => '3.2'),
                array('name' => 'p24_sign', 'value' => $crc)
            );

            $soap = new SoapClient("https://sandbox.przelewy24.pl/external/73622.wsdl");
            $res = $soap->RegisterTransaction("73622", "1a05b517a34b4e5e3ac5218f15d16ad0", $details);


            $check = $entityManager->getRepository(CoursePayment::class)->findOneBy(['participantCourse' => $participant_course->getId(), 'status' => CoursePayment::NOTPAID]);
            if (!$check) {
                $coursePayment = new CoursePayment();
                $coursePayment->setCourse($participant_course);
                $coursePayment->setToken($sessionId);
                $coursePayment->setStatus(CoursePayment::NOTPAID);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($coursePayment);
                $entityManager->flush();
        }else{
                $check->setCourse($participant_course);
                $check->setToken($sessionId);
                $check->setStatus(CoursePayment::NOTPAID);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($check);
                $entityManager->flush();
            }
            return $this->redirect('https://sandbox.przelewy24.pl/trnRequest/' . $res->result);
        }else
            return $this->redirect($this->generateUrl('course_index'));
    }

    /**
     * @Route("/payment/status", name="payment_status")
     */
    public function statusAction()
    {

        $request = Request::createFromGlobals();

        $p24_session_id = $request->request->get('p24_session_id', 'inne');
        $p24_amount = $request->request->get('p24_amount', 'inne');
        $p24_order_id = $request->request->get('p24_order_id', 'inne');

        $soap = new SoapClient("https://sandbox.przelewy24.pl/external/73622.wsdl");
        $res = $soap-> VerifyTransaction('73622', '1a05b517a34b4e5e3ac5218f15d16ad0', $p24_order_id, $p24_session_id, $p24_amount);

        $entityManager = $this->getDoctrine()->getManager();
        $course = $entityManager->getRepository(CoursePayment::class)->findOneBy(array("token"=>$p24_session_id));



        if ($res->error->errorCode) {
            $entityManager->remove($course);
            $entityManager->flush();
            echo 'Something went wrong: ' . $res->error->errorMessage;

        } else {
            $course->setStatus(CoursePayment::PAID);
            $course->setToken(null);

            $entityManager->persist($course);
            $entityManager->flush();
            $entityManager->clear();

        }


    }
}