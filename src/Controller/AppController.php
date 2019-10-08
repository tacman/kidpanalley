<?php

namespace App\Controller;

use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Yectep\PhpSpreadsheetBundle\Factory;

class AppController extends AbstractController
{
    /**
     * @var Spreadsheet
     */
    private $spreadsheet;
    /**
     * @var Factory
     */
    private $factory;

    public function __construct(Factory $factory)
    {
        $this->spreadsheet = $factory;
        $this->factory = $factory;
    }

    /**
     * @Route("/app", name="app")
     */
    public function index()
    {

        /** @var Xls $readerXlsx */
        $readerXlsx  = $this->spreadsheet->createReader('Xlsx');
        /** @var Spreadsheet $spreadsheet */
        try {
            $spreadsheet = $readerXlsx->load('/var/www/kpa/best-friends-credits.xlsx');
        } catch (\Exception $exception) {
            dd($exception);
        }

        /** @var Worksheet $sheet */
        $sheet = $spreadsheet->getActiveSheet();
            foreach ($sheet->toArray() as $idx=>$row) {
                if ($idx === 0) {
                    $header = $row;
                } else {
                    $data = array_combine($header, $row);
                    dump($data);
                }
            }
            dd($sheet);

        dd($spreadsheet);

        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }
}
