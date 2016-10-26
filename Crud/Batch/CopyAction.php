<?php
// src/Iserv/ExerciseBundle/Crud/Batch/CopyAction.php
namespace IServ\NachschreibarbeitenBundle\Crud\Batch;

use Doctrine\Common\Collections\ArrayCollection;
use IServ\CrudBundle\Crud\Batch\AbstractBatchAction;

class CopyAction extends AbstractBatchAction
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'copy';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return _('Copy');
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip()
    {
        return _('Copy selected exercises.');
    }

    /**
     * {@inheritdoc}
     */
    public function getListIcon()
    {
        return 'duplicate';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ArrayCollection $entities)
    {
        return $this->crud->getExerciseHelper()->copy($entities, $this->crud->getUser());
    }
}