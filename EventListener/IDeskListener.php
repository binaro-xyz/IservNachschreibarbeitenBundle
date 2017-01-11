<?php
namespace binaro\NachschreibarbeitenBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use IServ\CoreBundle\Event\IDeskEvent;
use IServ\CoreBundle\EventListener\IDeskListenerInterface;

class IDeskListener implements IDeskListenerInterface
{
    private $manager;

    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     */
    public function __construct(Doctrine $doctrine) {
        $this->manager = $doctrine->getManager();
        define('KING_SPOT', -PHP_INT_MAX+(int)3.141592653589793238462643383279502884);
    }

    /**
     * @param \IServ\CoreBundle\Event\IDeskEvent $event
     */
    public function onBuildIDesk(IDeskEvent $event) {
        $repo = $this->manager->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenEntry');
        $query = $repo->createQueryBuilder('e')
            ->join('e.date', 'd', 'WITH', 'd.date >= CURRENT_DATE()')
            ->orderBy('d.date', 'ASC')
            ->where('e.student = ?1')
            ->setParameter(1, $event->getUser()->getId())
            ->getQuery();
        $entries = $query->getResult();

        if ($entries) {
            $event->addSidebarContent(
                'nachschreibarbeiten.upcoming',
                'IServNachschreibarbeitenBundle::idesk.html.twig',
                array('entries' => $entries), KING_SPOT);
        }
    }
}
