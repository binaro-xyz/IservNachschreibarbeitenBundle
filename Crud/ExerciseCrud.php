<?php
// src/IServ/NachschreibarbeitenBundle/Crud/ExerciseCrud.php
namespace IServ\NachschreibarbeitenBundle\Crud;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormStaticControlType;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use IServ\CoreBundle\Entity\Group;
use IServ\CoreBundle\Entity\GroupRepository;
use IServ\CrudBundle\Crud\AbstractPersonalCrud;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use IServ\CrudBundle\Table\Filter\ListSearchFilter;
use IServ\CrudBundle\Table\ListHandler;
use IServ\NachschreibarbeitenBundle\Crud\Batch\CopyAction;
use IServ\NachschreibarbeitenBundle\Crud\Filter\ListGroupFilter;
use IServ\NachschreibarbeitenBundle\Entity\Exercise;
use IServ\NachschreibarbeitenBundle\Security\Privilege;
use IServ\NachschreibarbeitenBundle\Service\ExerciseHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ExerciseCrud extends AbstractPersonalCrud
{
    /**
     * @var \IServ\NachschreibarbeitenBundle\Service\ExerciseHelper
     */
    private $exerciseHelper;

    /**
     * @var Registry
     */
    private $doctrine;

	protected function configure()
	{
		$this->routesPrefix = 'manage/';
		$this->routesNamePrefix = 'manage_';
		$this->fieldOfOwnership = 'owner';
		$this->title = _('Manage');
		$this->itemTitle = _('Exercise');
        $this->options['help'] = 'v3/modules/pedagogy/exercise/';

		$this->templates['crud_show'] = 'IServExerciseBundle:Crud:show.html.twig';
        $this->templates['crud_edit'] = 'IServExerciseBundle:Crud:edit.html.twig';
        $this->templates['crud_index'] = 'IServExerciseBundle:Crud:index.html.twig';
	}

    public function prepareBreadcrumbs()
    {
        return [_('Exercises') => $this->router->generate('exercise_index')];
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('label' => _('Title'), 'responsive' => 'all', 'template' => 'IServExerciseBundle:Crud:field_title.html.twig'))
            ->add('startDate', 'datetime', array('label' => _('Start date'), 'group' => 'smartDate', 'moment' => true, 'responsive' => 'min-tablet'))
            ->add('endDate', 'datetime', array('label' => _('Deadline'), 'group' => 'smartDate', 'moment' => true))
            ->add('participants', 'entity', array('label' => _('Group')))
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
    	$showMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('startDate', 'datetime', array('label' => _('Start date')))
            ->add('endDate', 'datetime', array('label' => _('Deadline')))
            ->add('dateAdded', 'datetime', array('label' => _('Created on')))
            ->add('text', null, array('label' => _('Text')))
            ->add('participants', null, array('label' => _('Participants')))
            ->add('attachments', null, array('label' => _('Files')))
            ->add('submissions', null, array('label' => _('Submissions')))
    	;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $types = array(_('Files') => 'files', _('Text input') => 'text');

    	$formMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('startDate', DateTimeType::class, array('label' => _('Start date')))
            ->add('endDate', DateTimeType::class, array('label' => _('Deadline')))
        ;

        /* @var $exercise Exercise */
        if (null !== $exercise = $formMapper->getObject()) {
            $types = array_flip($types);
            $formMapper->add('type', FormStaticControlType::class, array(
                'label' => _('Submission'),
                'data' => $types[$exercise->getType()],
            ));
        }
        else {
            $formMapper->add('type', ChoiceType::class, array(
                'label' => _('Submission'),
                'choices' => $types,
                'choices_as_values' => true,
            ));
        };

        $formMapper
            ->add('participants', EntityType::class, array(
                'label' => _('Participants'),
                'class' => Group::class,
                'choice_label' => 'name',
                'select2' => true,
                'multiple' => true,
                'query_builder' => function(GroupRepository $er) {
                    return $er->createFindByFlagQueryBuilder(Privilege::FLAG_DOES_EXERCISES);
                },
            ))
            ->add('text', TextareaType::class, array(
                'label' => _('Description'),
                'attr' => ['rows' => 8],
            ))
    	;
    }

    public function configureListFilter(ListHandler $listHandler)
    {
        $groupFilter = new ListGroupFilter(_('Group'), 'participants', 'IServ\CoreBundle\Entity\Group', 'name', 'account');
        $groupFilter
            ->setUser($this->getUser())
            ->allowNone()
        ;
        $listHandler->addListFilter($groupFilter);

        $searchFilter = new ListSearchFilter(_('Title'), ['title']);
        $listHandler->addListFilter($searchFilter);

    }

    /**
     * {@inheritdoc}
     */
    protected function loadBatchActions()
    {
        parent::loadBatchActions();
        $this->batchActions->add(new CopyAction($this));

        return $this->batchActions;
    }

    /**
     * Retrieve the ExerciseHelper service
     *
     * @return \IServ\NachschreibarbeitenBundle\Service\ExerciseHelper
     */
    public function getExerciseHelper()
    {
        return $this->exerciseHelper;
    }

    /**
     * Set the ExerciseHelper service
     *
     * @param ExerciseHelper $exerciseHelper
     * @return ExerciseCrud
     */
    public function setExerciseHelper(ExerciseHelper $exerciseHelper)
    {
        $this->exerciseHelper = $exerciseHelper;

        return $this;
    }

    /**
     * Inject Doctrine service
     *
     * @param Registry $doctrine
     * @return ExerciseCrud
     */
    public function setDoctrine(Registry $doctrine)
    {
    	$this->doctrine = $doctrine;

    	return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRoutes()
    {
        parent::buildRoutes();

        $this->routes[self::ACTION_SHOW]['_controller'] = 'IServExerciseBundle:Crud:show';
        $this->routes[self::ACTION_EDIT]['_controller'] = 'IServExerciseBundle:Crud:edit';
    }

    /**
     * Returns an array of warnings
     *
     * Specifically it checks for submissions from last school year and displays a message
     * that hte user should delete those submissions for privacy reasons.
     *
     * @return string[]
     */
    public function getWarnings()
    {
    	$now = new \DateTime('now');

    	// If it's september we warn about submissions form the last school year
    	if (intval($now->format('m')) > 8) {
    		$year = intval($now->format('Y'));
    	} // otherwise we warn about everything which is from previous year
    	else {
    		$year = intval($now->format('Y')) - 1;
    	}

        /* @var $qb QueryBuilder */
		$qb = $this->doctrine->getManager()->createQueryBuilder();
		$qb
			->select('COUNT(1)')
			->from('IServExerciseBundle:ExerciseSubmission', 'sub')
			->join('IServExerciseBundle:Exercise', 'e', Expr\Join::WITH, 'sub.exercise = e.id')
			->where('e.owner = :owner')
			->andWhere('sub.time < :time')
			->setParameter('owner', $this->getUser())
			->setParameter('time', new \DateTime(sprintf('%d-08-01', $year)))
		;

		$warnings = array();
		if ($qb->getQuery()->getSingleScalarResult()) {
			$warnings[] = _('You still have stored submissions from the last school year. You should delete these for reasons of privacy as soon as possible.');
		}

		return $warnings;
    }

}
