<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use KevinPapst\AdminLTEBundle\Event\KnpMenuEvent;

class MenuSubscriber implements EventSubscriberInterface
{
    public function onKnpMenuEvent(KnpMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild('survos_landing', ['label' => 'home', 'route' => 'app_homepage'])->setAttribute('icon', 'fas fa-home');
        $menu->addChild('songs_credits', ['route' => 'app_credits_page'])->setAttribute('icon', 'fal fa-music');

        $songMenu = $menu->addChild('song');
        $songMenu->addChild('song.list', ['route' => 'song_index']);
        $songMenu->addChild('song.new', ['route' => 'song_new']);

        $loadMenu = $menu->addChild('load');
        $loadMenu->addChild('app_load_songs', ['route' => 'app_load_songs'])->setAttribute('icon', 'fas fa-home');
        $loadMenu->addChild('app_load_from_files', ['route' => 'app_load_from_files'])->setAttribute('icon', 'fas fa-music');
        $loadMenu->addChild('app_load_youtube_channel', ['route' => 'app_load_youtube_channel'])->setAttribute('icon', 'fab fa-youtube');
        $menu->addChild('admin', ['route' => 'easyadmin'])->setAttribute('icon', 'fas fa-wrench');

        $menu->addChild('survos_landing_credits', ['route' => 'survos_landing_credits'])->setAttribute('icon', 'fas fa-trophy');

        // ...
    }

    public static function getSubscribedEvents()
    {
        return [
            KnpMenuEvent::class => 'onKnpMenuEvent',
        ];
    }
}
