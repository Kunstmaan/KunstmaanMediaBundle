<?php

namespace Kunstmaan\MediaBundle\EventListener;

use Kunstmaan\AdminBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{

    public function __construct()
    {

    }

    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $factory = $event->getFactory();

        $mediaMenu = $menu->addChild($factory->createItem('Media', array('route' => 'KunstmaanMediaBundle_folder_show', 'routeParameters' => array('folderId' => '1'))));
        $mediaMenu->setExtra('folderId', '1');
    }
}