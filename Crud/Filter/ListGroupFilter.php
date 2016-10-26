<?php
// src/IServ/ExerciseBundle/Crud/Filter/ListGroupFilter.php
namespace IServ\NachschreibarbeitenBundle\Crud\Filter;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use IServ\CoreBundle\Entity\User;
use IServ\CrudBundle\Table\Filter\ListAssociationFilter;

/**
 * Injects the current user's exercises into the property filter.
 */
class ListGroupFilter extends ListAssociationFilter
{
    private $user;

    /**
     * Injects the current user
     *
     * @param User $user
     * @return \IServ\NachschreibarbeitenBundle\Crud\Filter\ListGroupFilter
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Is executed by CrudController:indexAction() to load options for filter.
     *
     * Parameter is a QueryBuilder from the EntityRepository of the class with 'filter' as SELECT alias.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @return \IServ\NachschreibarbeitenBundle\Crud\Filter\ListGroupFilter
     */
    public function loadOptions(QueryBuilder $qb)
    {
        $sub = clone $qb;
        $sub
            ->resetDQLParts()
            ->select('g')
            ->from('IServExerciseBundle:Exercise', 'e')
            ->join('e.participants', 'g')
            ->where('e.owner = :user')
        ;

        $qb
            ->select('filter.'.$this->idProperty.' AS value, filter.'.$this->titleProperty. ' AS name')
            ->distinct()
            ->where('filter IN ('.$sub->getDQL().')')
            ->orderBy('filter.'.$this->titleProperty)
        ;

        // Ensure user is set
        if (null === $this->user) {
            throw new \RuntimeException('Missing mandatory `user` parameter!');
        }
        $qb->setParameter('user', $this->user);

        $qb->andWhere('filter.'.$this->idProperty.' IS NOT NULL');

        return $this->setOptions($qb->getQuery()->getResult(Query::HYDRATE_ARRAY));
    }
}