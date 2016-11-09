<?php
// src/IServ/NachschreibarbeitenBundle/Controller/ExerciseController.php
namespace IServ\NachschreibarbeitenBundle\Controller;

use IServ\CoreBundle\Controller\PageController;
use IServ\CoreBundle\Entity\User;
use IServ\NachschreibarbeitenBundle\Entity\NachschreibarbeitenDate;
use IServ\NachschreibarbeitenBundle\Entity\NachschreibarbeitenEntry;
use IServ\NachschreibarbeitenBundle\Security\Privilege;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("nachschreibarbeiten")
 */
class NachschreibarbeitenController extends PageController {
    /**
     * @Route("", name="nachschreibarbeiten_index")
     * @Template()
     * @return array|RedirectResponse
     */
    public function indexAction(Request $request, $path) {
        if(!$this->isGranted(Privilege::ACCESS_NACHSCHREIBARBEITEN)) {
            throw $this->createAccessDeniedException("You are not allowed to view this page.");
        } else {
            $repo = $this->getDoctrine()->getManager()->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenDate');

//            $nd = new NachschreibarbeitenEntry();
//            $nd->setDate($repo->find(1));
//            $nd->setOwner($this->getUser());
//            $nd->setAdditionalMaterial('');
//            $nd->setClass('05Gpi');
//            $nd->setDuration(3141);
//            $nd->setStudent($this->getUser());
//            $nd->setSubject('Pi');
//            $nd->setTeacher($this->getUser());
//
//
//            $this->getDoctrine()->getManager()->persist($nd);
//            $this->getDoctrine()->getManager()->flush();

            $anderes_repo = $this->getDoctrine()->getManager()->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenEntry');
            return array(
                'dummy' => $repo->findAll(),
                'anderes_repo' => $anderes_repo->findAll(),
                'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index'))),
                'menu' => $this->createMenu('index')
            );
        }
    }

    /**
     * @Route("/dates", name="nachschreibarbeiten_dates_manage")
     * @Template()
     * @return array|RedirectResponse
     */
    public function dateManageAction(Request $request) {
        if(!$this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            throw $this->createAccessDeniedException("You are not allowed to view this page. YOU ARE NOT KING!!!");
        } else {
            $repo = $this->getDoctrine()->getManager()->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenDate');

            return array(
                'dates' => $repo->findAll(),
                'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index')), array('name' => _('Nachschreibarbeitentermine'), 'url' => $this->generateUrl('nachschreibarbeiten_dates_manage'))),
                'menu' => $this->createMenu('dates')
            );
        }
    }

    /**
     * @Route("/dates/edit/{id}", name="nachschreibarbeiten_dates_edit")
     * @Template()
     * @return array|RedirectResponse
     */
    public function dateEditAction(Request $request, $id) {
        if(!$this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            throw $this->createAccessDeniedException("You are not allowed to view this page. YOU ARE NOT KING!!!");
        } else {
            return array(
                'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index')), array('name' => _('Nachschreibarbeitentermine'), 'url' => $this->generateUrl('nachschreibarbeiten_dates_manage')), array('name' => _('Nachschreibarbeitentermin bearbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_dates_edit', array('id' => $id)))),
                'menu' => $this->createMenu('dates')
            );
        }
    }

    private function createMenu($path) {
        $menu = $this->get('knp_menu.factory')->createItem('page');

        $menu->addChild('nachschreibarbeiten_show', array(
            'label' => _('Entries'),
            'route' => 'nachschreibarbeiten_index',
            'extras' => ['icon' => 'molecule', 'icon_style' => 'fugue'],
            'current' => ('index' == $path),
        ));

        if($this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $menu->addChild('nachschreibarbeiten_dates', array(
                'label' => _('Nachschreibarbeitentermine'),
                'route' => 'nachschreibarbeiten_dates_manage',
                'extras' => ['icon' => 'flask', 'icon_style' => 'fugue'],
                'current' => ('dates' == $path),
            ));
        }

        return $menu;
    }

}
