<?php

namespace Kunstmaan\MediaBundle\EventListener;

use Kunstmaan\AdminBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $mediaMenu = $event->getMenu()->addChild($event->getFactory()->createItem('Media', array('route' => 'KunstmaanMediaBundle_folder_show', 'routeParameters' => array('folderId' => '1'))));
        $mediaMenu->setExtra('folderId', '1');
    }
}