<?php
// src/IServ/ExerciseBundle/Controller/AbstractExerciseController.php
namespace IServ\NachschreibarbeitenBundle\Controller;

use IServ\CoreBundle\Controller\PageController;
use IServ\NachschreibarbeitenBundle\Entity\Exercise;
use IServ\NachschreibarbeitenBundle\Entity\ExerciseRepository;
use IServ\NachschreibarbeitenBundle\Service\ExerciseHelper;

/**
 * Basic stuff for exercise controlling
 */
class AbstractExerciseController extends PageController
{
    /**
     * @var ExerciseHelper
     */
    protected $helper;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $exerciseRepo;

    /**
     * Gets exercise by ID and verifies ownership
     *
     * @param $id
     * @return Exercise
     */
    protected function getExercise($id)
    {
        $exercise = $this->getRepo()->find($id);

        if ($exercise->getOwner() != $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to manage this exercise');
        }

        return $exercise;
    }

    /**
     * Get the exercise repository
     *
     * @return ExerciseRepository
     */
    protected function getRepo()
    {
        if (null === $this->exerciseRepo) {
            $this->exerciseRepo = $this->getDoctrine()->getManager()->getRepository('IServExerciseBundle:Exercise');
        }

        return $this->exerciseRepo;
    }

    /**
     * Initialize the helper service
     *
     * @return ExerciseHelper
     */
    protected function getHelper()
    {
        if (null == $this->helper) {
            $this->helper = $this->get('iserv.exercise.helper');
        }

        return $this->helper;
    }


}
