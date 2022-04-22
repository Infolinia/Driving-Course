<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Details;
use AppBundle\Form\ResettingPasswordType;
use AppBundle\Entity\User;
use AppBundle\Service\EmailService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResettingPasswordController extends Controller {

    /**
     * @Route("/resetting", name="resettingPassword_reset")
     * @param Request $request
     * @return Response
     */
    public function resetAction(Request $request) {

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('course_index'));
        }

        $form = $this->createForm(ResettingPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form["email"]->getData();
            $detail = $this->getDoctrine()->getRepository(Details::class)->findOneBy(['email' => $email]);

            if($detail != null){
                $user = $detail->getOwner();
                $token = md5(uniqid(rand(), true));
                $user->setToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $emailService = $this->get(EmailService::class);
                $emailService->sendResetPasswordTokenEmail($token, $user);
                $this->addFlash("success", "Na adres email został wysłany link zmieniajacy hasło.");
            }else {
                $this->addFlash("error", "Użytkownik z podanym adresem email nie istnieje.");
            }
        }

        return $this->render(
            'security/resetting.twig', array('form' => $form->createView())
        );
    }

    /**
     * @Route("/resetting/confirm/{token}", name="resettingPassword_confirm", methods={"GET"})
     * @param $token
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @throws NonUniqueResultException
     */
    public function confirmAction($token, UserPasswordEncoderInterface $passwordEncoder) {

        $user = $this->getDoctrine()->getRepository(User::class)->loadUserByToken($token);

        if($user === null) {
            $this->addFlash("error", "Podany token wygasł lub nie istnieje.");
            return $this->redirect($this->generateUrl('course_index'));
        }

        $newPassword = substr(md5(uniqid(rand(), true)), 0, 12);
        $password = $passwordEncoder->encodePassword($user, $newPassword);
        $user->setPassword($password);

        $user->setToken(null);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $emailService = $this->get(EmailService::class);
        $emailService->sendNewPasswordEmail($newPassword, $user);
        $this->addFlash("success", "Na adres email zostało wysłane nowe hasło.");

        return $this->redirect($this->generateUrl('resettingPassword_reset'));
    }
}