<?php
namespace IServ\NachschreibarbeitenBundle\Service;

use IServ\CrudBundle\Doctrine\ORM\ORMObjectManager;
use IServ\CrudBundle\Exception\ObjectManagerException;
use IServ\NachschreibarbeitenBundle\Entity\Exercise;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExerciseManager extends ORMObjectManager
{
    /**
     * @var ExerciseHelper
     */
    private $helper;

    /**
     * The constructor
     *
     * @param RegistryInterface $registry
     * @param ExerciseHelper $helper
     */
    public function __construct(RegistryInterface $registry, ExerciseHelper $helper)
    {
        parent::__construct($registry);

        $this->helper = $helper;
    }

    /**
     * Delete an exercise
     *
     * @see \IServ\CrudBundle\Doctrine\ORM\ORMObjectManager::delete()
     * @throws ObjectManagerException
     *
     * @param Exercise $object
     *
     * @return bool
     *
     *
     */
    public function delete($object)
    {
        $entityManager = $this->getEntityManager($object);

        foreach ($object->getSubmissions() as $submission) {
            $this->helper->deleteFromExercise($submission, 'submission');
        }
        foreach ($object->getAttachments() as $attachment) {
            $this->helper->deleteFromExercise($attachment, 'attachment');
        }

        try {
            $entityManager->remove($object);
            $entityManager->flush();
        }
        catch (\PDOException $e) {
            throw new ObjectManagerException('Could not delete object', 0, $e);
        }

        return true;
    }

}