<?php
// src/IServ/ExerciseBundle/Controller/ExerciseController.php
namespace IServ\NachschreibarbeitenBundle\Controller;

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
class NachschreibarbeitenController extends AbstractNachschreibarbeitenController
{
    /**
     * @Route("", name="nachschreibarbeiten_index")
     * @Template()
     * @return array|RedirectResponse
     */
    public function indexAction()
    {
      if(!$this->isGranted(Privilege::ACCESS_NACHSCHREIBARBEITEN)) {
        throw $this->createAccessDeniedException("You are not allowed to view this page.");
      } else {
        return array();
      }
    }
}
