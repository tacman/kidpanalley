<?php


namespace App\Services;


use App\Entity\Song;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Yectep\PhpSpreadsheetBundle\Factory;

class AppService
{

    /**
     * @var EntityManagerInterface
     */
    private $em;
    private $serializer;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Factory
     */
    private $spreadsheet;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, Factory $spreadsheet, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->spreadsheet = $spreadsheet;
    }

    public function loadSongs()
    {
        /** @var Xls $readerXlsx */
        $readerXlsx  = $this->spreadsheet->createReader('Xls');
        /** @var Spreadsheet $spreadsheet */
        try {
            $spreadsheet = $readerXlsx->load(__DIR__ . '/../../data/kpa-songs.xls');
        } catch (\Exception $exception) {
            dd($exception);
        }

        /** @var Worksheet $sheet */
        $sheet = $spreadsheet->getActiveSheet();
        $songs = [];
        $lyrics = [];
        $header = [];

        foreach ($sheet->toArray() as $idx=>$row) {
            if ($idx === 0) {
                $header = $row;
            } else {
                $data = array_combine($header, $row);
                if (!$data['Instrumentals']) {
                    continue;
                }
                $song = (new Song())
                    ->setTitle($data['Instrumentals'])
                    ->setSchool($data['school'])
                    ->setWriters($data['writer']);

                $em = $this->em;
                $logger = $this->logger;
                $em->persist($song);
                if ($data['date']) {
                    try {
                        $song
                            ->setDate(new \DateTimeImmutable($data['date']));
                        $song->setYear((int)$song->getDate()->format('Y'));
                    } catch (\Exception $e) {
                        $logger->error("Line $idx: Can't set date " . $data['date'] . ' on ' . $song->getTitle());
                    }
                }
                if ($data['year']) {
                    $song
                        ->setYear((int)$data['year']);
                }

                $song->setNotes(json_encode($data));
                array_push($songs, $song);
                // dump($data);
            }
            if ($idx == 45) {
                // dd($data, $song);
                // break;
            }
        }

        $em->flush();
    }

    /**
     * @Cache(expires="tomorrow", public=true)
     */
    private function fetchUrl($url)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        $list = $response->toArray();

        $list = json_decode(json_encode($list), FALSE);
        return $list;

    }

    public function fetchYoutubeChannel($key, $channelId)
    {
        $videos = [];
        $next = '';
        $repo = $this->em->getRepository(Video::class);
        do {
            $url = sprintf("https://www.googleapis.com/youtube/v3/search?part=id,snippet&type=video&maxResults=50&channelId=$channelId&type=video&key=$key&pageToken=$next");
            $list = $this->fetchUrl($url);
            $next = $list->nextPageToken ?? false;
            foreach ($list->items as $item) {
                $item = (object) $item;
                $id = $item->id->videoId;
                if (!$video = $repo->findOneBy(['youtubeId' => $id])) {
                    $video = (new Video())
                        ->setYoutubeId($id);
                    $this->em->persist($video);
                }
                $snippet = $item->snippet;
                $video
                    ->setTitle($snippet->title)
                    ->setDescription($snippet->description);
                $video->setDate(new \DateTimeImmutable($snippet->publishedAt));

                array_push($videos, $video);
            }

        } while ($next);
        $this->em->flush();
        // dd($list);

        return $videos;

        // return $response->getContent();

    }
}