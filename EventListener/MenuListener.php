<?php
// src/IServ/NachschreibarbeitenBundle/EventListener/MenuListener.php
namespace IServ\NachschreibarbeitenBundle\EventListener;

use IServ\CoreBundle\Event\MenuEvent;
use IServ\CoreBundle\EventListener\MainMenuListenerInterface;
use IServ\NachschreibarbeitenBundle\Security\Privilege;

class MenuListener implements MainMenuListenerInterface
{
    /**
     * @param \IServ\CoreBundle\Event\MenuEvent $event
     */
    public function onBuildMainMenu(MenuEvent $event) {
        // Get menu from event and add admin link(s)
        $menu = $event->getMenu(self::ORGANISATION);

        if($event->getAuthorizationChecker()->isGranted(Privilege::ACCESS_NACHSCHREIBARBEITEN) || $event->getAuthorizationChecker()->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $menu
                ->addChild('nachschreibarbeiten', array(
                    'route' => 'nachschreibarbeiten_index',
                    'label' => _('Nachschreibarbeiten')
                ))
                ->setExtra('orderNumber', 20)
                ->setExtra('icon', 'molecule')
                ->setExtra('icon_style', 'fugue');
        }
    }
}
