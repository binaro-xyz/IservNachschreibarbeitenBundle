<?php
// src/IServ/ExerciseBundle/EventListener/IDeskListener.php
namespace IServ\NachschreibarbeitenBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use IServ\CoreBundle\Event\IDeskEvent;
use IServ\CoreBundle\EventListener\IDeskListenerInterface;
use IServ\NachschreibarbeitenBundle\Security\Privilege;

class IDeskListener implements IDeskListenerInterface
{
    private $doctrine;

    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     */
    public function __construct(Doctrine $doctrine)
    {
        $this->doctrine = $doctrine->getManager();
    }

    /**
     * @param \IServ\CoreBundle\Event\IDeskEvent $event
     */
    public function onBuildIDesk(IDeskEvent $event)
    {
        if( $event->getAuthorizationChecker()->isGranted(Privilege::EXCLUDED_FROM_EXERCISES)) {
            return;
        }

        // Get upcoming exercises
        $limit = 3;
        $exercises = $this->doctrine->getRepository('IServExerciseBundle:Exercise')->findUpcomingExercisesForUser($event->getUser(), $limit);

        // Inject into IDesk
        if ($exercises) {
            $event->addContent(
                'exercises.upcoming',
                'IServExerciseBundle::idesk.html.twig',
                array(
                    'exercises' => $exercises,
                    'limit' => $limit,
                ),
                -1
            );
        }
    }
}
