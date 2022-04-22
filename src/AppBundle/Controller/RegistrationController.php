<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Details;
use AppBundle\Form\RegisterType;
use AppBundle\Entity\User;
use AppBundle\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegistrationController extends Controller {

    /**
     * @Route("/register", name="registration_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('course_index'));
        }

        $user = new User();
        $details = new Details();
        $details->setOwner($user);

        $user->setDetails($details);
        $form = $this->createForm(RegisterType::class, array('user'=>$user, 'details'=>$details));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($details);
            $entityManager->persist($user);
            $entityManager->flush();

            $emailService = $this->get(EmailService::class);
            $emailService->sendRegisterEmail($user);

            $this->addFlash("success", "Użytkownik został utworzony pomyślnie.");

            return $this->redirect($this->generateUrl('security_login'));
        }

        return $this->render(
            'security/register.html.twig', array('form' => $form->createView())
        );
    }
}