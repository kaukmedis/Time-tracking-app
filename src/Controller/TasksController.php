<?php

namespace App\Controller;

use App\Entity\Tasks;
use App\Form\TasksType;
use App\Repository\TasksRepository;
use App\Service\TasksReport;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/tasks")
 */
class TasksController extends AbstractController
{
    /**
     * @Route("/", name="tasks_index", methods={"GET"})
     */
    public function index(): Response
    {

        return $this->render('tasks/index.html.twig', [
        ]);
    }

    /**
     * @Route("/data", name="tasks_data", methods={"GET"})
     * @param TasksRepository $tasksRepository
     * @return Response
     */
    public function data(TasksRepository $tasksRepository){
        $tasks = $tasksRepository->findBy(['User'=> $this->getUser()]);

        $encoders = [ new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize(array('data' => $tasks), 'json');
        return new Response($jsonContent);
    }

    /**
     * @Route("/new", name="tasks_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $task = new Tasks();
        $form = $this->createForm(TasksType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('tasks_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tasks/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/report", name="tasks_report", methods={"GET","POST"})
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param TasksReport $tasksReport
     * @return Response
     */
    public function generateReport(Request $request,TranslatorInterface $translator,TasksReport $tasksReport){
        $form = $this->createFormBuilder()
            ->add('from', DateType::class,[
                'widget' => 'single_text','attr'   => ['max' => '9999-12-01'],
                'format' => 'yyyy-MM-dd',
                'required'  => true,
                'label' => 'from'
            ])
            ->add('to', DateType::class,[
                'widget' => 'single_text','attr'   => ['max' => '9999-12-01'],
                'format' => 'yyyy-MM-dd',
                'required'  => true,
                'label' => 'to'
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type =$request->request->get('type');
            $from = $form->getData()['from']->format('Y-m-d');
            $to = $form->getData()['to']->format('Y-m-d');
            switch ($type){
                case $translator->trans('pdf'):
                    return $tasksReport->pdfReport($from,$to);
                    break;
                case $translator->trans('excel'):
                    return $tasksReport->excelReport($from,$to);
                    break;
                case $translator->trans('csv'):
                    return $tasksReport->csvReport($from,$to);
                    break;
                default:

                    break;
            }

        }
        else{
            return $this->render('tasks/report.html.twig', [
                'form' => $form->createView(),
            ]);
        }

    }

    /**
     * @Route("/{id}", name="tasks_show", methods={"GET"})
     */
    public function show(Tasks $task): Response
    {
        return $this->render('tasks/show.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tasks_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Tasks $task
     * @return Response
     */
    public function edit(Request $request, Tasks $task): Response
    {
        $form = $this->createForm(TasksType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tasks_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tasks/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tasks_delete", methods={"POST"})
     * @param Request $request
     * @param Tasks $task
     * @return Response
     */
    public function delete(Request $request, Tasks $task): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tasks_index', [], Response::HTTP_SEE_OTHER);
    }
}
