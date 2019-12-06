<?php

namespace App\Controller;

use App\Entity\Song;
use App\Services\DocxConversion;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
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
    /**
     * @var Environment
     */
    private $twig;

    private $auth;

    const ENDPOINT = 'https://www.kidpanalley.org/wp-json/wp/v2/pages';

    public function __construct(Factory $factory, Environment $twig)
    {
        $this->spreadsheet = $factory;
        $this->factory = $factory;
        $this->twig = $twig;


    }

    private function getAuth()
    {
        $u = $this->getParameter('api_username');
        $p = $this->getParameter('api_password');
        return [$u, $p];
    }

    private function loadLyrics()
    {
        $file = '../bf-lyrics.docx';
        if (!file_exists($file)) {
            throw new \Exception("File $file does not exist");
        }
        $converter = new DocxConversion($file);
        $text = $converter->convertToText();
        dd($text);
        $client = HttpClient::create();

        $endPoint = self::ENDPOINT . '/1870';

        $results = $client->request('GET', $endPoint, $data = [
            'auth_basic' => $this->getAuth(),
        ]);

        $lyricsPage = json_decode($results->getContent());

        dd($lyricsPage->content->rendered);

    }

    private function createPage(Song $song)
    {

        $content = $this->twig->render("song.html.twig", [
            'song' => $song,
        ]);


        $wordpressPagePayload = [
            'type' => 'page',
            'title' => $song->getTitle(),
            'content' => $content
        ];

        $client = HttpClient::create();

        $method = 'POST';
        $endPoint = self::ENDPOINT;

        if ($wordpressId = $song->getWordpressPageId()) {
            // update instead of create
            $endPoint .= '/' . $wordpressId;
            // add id?
           // $method = 'PUT';
        } else {

        }
        $results = $client->request($method, $endPoint, $data = [
            'auth_basic' => $this->getAuth(),
            'json' => $wordpressPagePayload
        ]);

        /*
        $command = sprintf('curl -H "Accept: application/json" -H "Content-Type: application/json" -X POST -d \'{"title":"Test Page","content":"lyrics go here.","type":"page"}\' %s/wp-json.php/posts -u %s:%s',
           'https://www.kidpanalley.org', $u, $p);
        dump($command);

        $results = exec($command);
        */

        $response = json_decode($results->getContent());

        $id = $response->id;
        $song->setWordpressPageId($id);


        dump($id, $endPoint, $data, $results, $response);




        // $response = $client->request('POST', );


    }
    /**
     * @Route("/app", name="app")
     */
    public function index(EntityManagerInterface $em)
    {

        $lyrics = $this->loadLyrics();

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
                    $title = $data['Song Title'];
                    // look for the title
                    if (!$song = $em->getRepository(Song::class)->findOneBy(['title' => $title])) {
                        $song = (new Song())
                            ->setTitle($title);
                        $em->persist($song);
                    }
                    $song
                        ->setWriters($data['Writers'])
                        ->setMusicians($data['Musicians'])
                        ->setFeaturedArtist($data['Featured Artist']);
                    $this->createPage($song);
                    $em->flush();
                    dd($song);
                }
            }
            dd($sheet);

        dd($spreadsheet);

        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }
}
