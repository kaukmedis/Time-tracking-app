<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/17/2020
 * Time: 11:20 AM
 */

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class UserController
 * @package App\Controller
 * @Route("user")
 */
class UserController extends AbstractController
{

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/register", name="user_registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user,array(
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $currentTime = new DateTime('NOW');
            date_timezone_set($currentTime,timezone_open('Europe/Vilnius'));
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $entityManager = $this->getDoctrine()->getManager();
            $user->setCreatedAt($currentTime);
            $user->setUpdatedAt($currentTime);
            $user->setRoles(array('ROLE_USER'));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('tasks_index');
        }
        return $this->render(
            'security/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
//        return $this->render('security/login.html.twig');
    }
}