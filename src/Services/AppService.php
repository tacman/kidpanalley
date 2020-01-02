<?php


namespace App\Services;


use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;

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

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->logger = $logger;
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
                if (!$video = $repo->findBy(['youtubeId' => $id])) {
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