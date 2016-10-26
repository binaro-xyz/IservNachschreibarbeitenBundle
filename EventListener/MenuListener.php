<?php
// src/IServ/ExerciseBundle/EventListener/MenuListener.php
namespace IServ\NachschreibarbeitenBundle\EventListener;

use IServ\CoreBundle\Event\MenuEvent;
use IServ\CoreBundle\EventListener\MainMenuListenerInterface;

class MenuListener implements MainMenuListenerInterface
{
    /**
     * @param \IServ\CoreBundle\Event\MenuEvent $event
     */
    public function onBuildMainMenu(MenuEvent $event) {
      // Get menu from event and add admin link(s)
      $menu = $event->getMenu(self::ORGANISATION);
      $menu
          ->addChild('nachschreibarbeiten', array(
              'route' => 'nachschreibarbeiten_index',
              'label' => _('Nachschreibarbeiten')
          ))
          ->setExtra('orderNumber', 20)
          ->setExtra('icon', 'calendar-task')
          ->setExtra('icon_style', 'fugue');

    }
}
