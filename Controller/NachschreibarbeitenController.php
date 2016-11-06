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
    public function indexAction() {
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
            return array('dummy' => $repo->findAll(), 'anderes_repo' => $anderes_repo->findAll(), 'exercises' => array());
        }
    }
}
