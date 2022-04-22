<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Details;
use AppBundle\Entity\Photo;
use AppBundle\Entity\User;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\PhotoType;
use AppBundle\Model\ChangePassword;
use AppBundle\Form\ProfileType;
use AppBundle\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class ProfileController extends Controller {

    /**
     * @Route("/profile", name="profile_index")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN", "ROLE_INSTRUCTOR", "ROLE_PARTICIPANT"]);
        $user = $this->getUser();
        $photo = new Photo();
        $photo_f = $this->createForm(PhotoType::class, $photo);
        $photo_f->handleRequest($request);

        $form = $this->createForm(ProfileType::class, array('details'=>$user->getDetails()), array('roles'=>$user->getRoles()[0]));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash("success", "Dane zostały zmienione pomyślnie.");
            return $this->redirectToRoute("profile_index");
        }

        if ($photo_f->isSubmitted() && $photo_f->isValid()) {
            /** @var UploadedFile */
            $file = $photo->getImage();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            try {

                $file->move(
                    $this->getParameter('photos_directory'),
                    $fileName
                );

                $entityManager = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $p = $entityManager->getRepository(Photo::class)->findOneBy(["owner" => $user->getId()]);

                if($p != null){
                    $p->setImage($fileName);
                    $entityManager->persist($p);
                }else {
                    $photo->setOwner($user);
                    $photo->setImage($fileName);
                    $entityManager->persist($photo);
                }
                $photo->setImage($fileName);
                $entityManager->flush();
                $this->addFlash("success", "Zdjecie profilowe zostało dodane.");

            } catch (FileException $e) {
                $this->addFlash("error", "Błąd dodawania zdjęcia: ".$e);
            }

            return $this->redirect($this->generateUrl('profile_index'));
        }

        return $this->render('profile/show.html.twig', array(
            'form' => $form->createView(),
            'user' =>$user,
            'photo_f' => $photo_f->createView(),
        ));
    }

    /**
     * @Route("/profile/change/password", name="profile_change")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function changeAction(Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        $this->denyAccessUnlessGranted(["ROLE_ADMIN", "ROLE_INSTRUCTOR", "ROLE_PARTICIPANT"]);
        $changePasswordModel = new ChangePassword();
        $form = $this->createForm(ChangePasswordType::class, $changePasswordModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $user = $this->getUser();
            $password = $passwordEncoder->encodePassword($user, $changePasswordModel->getNewPassword());
            $user->setPassword($password);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash("success", "Twoje hasło zostało zmienione pomyślnie.");
            $emailService = $this->get(EmailService::class);
            $emailService->sendNewPasswordEmail($changePasswordModel->getNewPassword(), $user);
            return $this->redirectToRoute("profile_index");
        }

        return $this->render('profile/password.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}