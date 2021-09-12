<?php
namespace App\Service;
use App\Entity\Tasks;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;

use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/11/2021
 * Time: 11:19 AM
 */

class TasksReport
{
    /**
     * @var EntityManagerInterface $doctrine
     */
    protected $doctrine;
    /**
     * @var \Twig_Environment
     */
    protected $twig;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $doctrine, \Twig\Environment $twig,TranslatorInterface $translator,Security $security)
    {
        $this->doctrine = $doctrine;
        $this->twig=$twig;
        $this->translator=$translator;
        $this->security=$security;
    }
    private function tasksReportData($from,$to){
        $tasksRepository = $this->doctrine->getRepository(Tasks::class);
        /**
         * @var User
         */
        $user = $this->security->getUser();
        $headers[] = [
            $this->translator->trans('title'),
            $this->translator->trans('comment'),
            $this->translator->trans('date'),
            $this->translator->trans('time_spent'),
        ];
        $data = $tasksRepository->getUserTasks($user->getId(),$from,$to);
        $data = array_merge($headers, $data);

        $sum = $tasksRepository->getUserTasksTimeSpentSum($user->getId(),$from,$to);
        $data[]=['','', $this->translator->trans('time_spent_sum'),$sum];
        return $data;
    }

    public function pdfReport($from, $to){
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVuSans');
        $dompdf = new Dompdf($pdfOptions);
        $tasksData = $this->tasksReportData($from,$to);
        $html = $this->twig->render('pdfTemplates/tasksReport.html.twig', [
            'tasksData' => $tasksData
        ]);
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $fileName=$this->translator->trans('tasks_report').' '.date("Y-m-d").'.pdf';
        $output =$dompdf->output();
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'attachment; filename="'.$fileName.'"',
        ]);
    }
    public function excelReport($from, $to){
        $tasksData = $this->tasksReportData($from,$to);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($tasksData);
        $writer = new Xlsx($spreadsheet);


        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $fileName=$this->translator->trans('tasks_report').' '.date("Y-m-d").'.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }
    public function csvReport($from, $to){
        $tasksData = $this->tasksReportData($from,$to);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($tasksData);
        $writer = new Csv($spreadsheet);
        $writer->setUseBOM(true);
        $writer->setDelimiter(';');
        $writer->setSheetIndex(0);


        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $fileName=$this->translator->trans('tasks_report').' '.date("Y-m-d").'.csv';
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }
}