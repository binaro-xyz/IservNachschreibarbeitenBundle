<?php
// src/IServ/ExerciseBundle/Controller/ExerciseController.php
namespace IServ\NachschreibarbeitenBundle\Controller;

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
     return array();
    }
}
