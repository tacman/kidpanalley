<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailLoginAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,
                             UserProviderInterface $userProvider,
                             UserRepository $userRepository,
                             GuardAuthenticatorHandler $guardHandler, EmailLoginAuthenticator $authenticator): Response
    {


        /** @var User $email */
        if ($email = $request->get('email')) {
            // still redirects to login!!
//            if (!$user = $userProvider->loadUserByUsername($email)) {
            if (!$user = $userRepository->findOneBy(['email' => $email]))
            {
                $user = (new User())
                    ->setEmail($email);
            }
        } else {
            $user = new User();
        }

        $clientKey = $request->get('clientKey');
        $token = $request->get('token');
        $user->setToken($clientKey, $token);

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


//        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
