<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/3/2020
 * Time: 9:24 AM
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET","POST"})
     */
    public function home(){
        return $this->redirectToRoute('tasks_index');
    }
}