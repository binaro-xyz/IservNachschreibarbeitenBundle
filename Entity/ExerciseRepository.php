<?php
// src/IServ/ExerciseBundle/Entity/ExerciseRepository.php
namespace IServ\NachschreibarbeitenBundle\Entity;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use IServ\CoreBundle\Entity\User;
use IServ\CrudBundle\Doctrine\ORM\EntitySpecificationRepository;
use IServ\NachschreibarbeitenBundle\Security\Privilege;

/**
 * IServExerciseBundle:ExerciseRepository
 */
class ExerciseRepository extends EntitySpecificationRepository
{
    /**
     * Looks up exercises to user has to do in the next $limit days.
     *
     * @param User $user
     * @param integer $limit
     * @return array
     */
    public function findUpcomingExercisesForUser(User $user, $limit)
    {
        $qb = $this->createQueryBuilder('e');

        $qb
            ->select('e, o')
            ->join('e.owner', 'o')
            ->join('e.participants', 'p')
            ->leftJoin('e.submissions', 's', Expr\Join::WITH, 's.exercise = e AND s.user = :user')
            ->where('e.startDate <= CURRENT_TIMESTAMP()')
            ->andWhere("CURRENT_TIMESTAMP() <= DATE_ADD(e.endDate, e.tolerance, 'day')")
            ->andWhere("CURRENT_TIMESTAMP() >= DATE_SUB(e.endDate, :limit, 'day')")
            ->andWhere('p IN (:groups)')
            ->andWhere('s.id IS NULL')
            ->orderBy('e.endDate')
            ->setParameter('user', $user)
            ->setParameter('limit', $limit)
            ->setParameter('groups', $user->getGroups()->toArray())
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Count exercises to user has to do
     *
     * @param User $user
     * @return integer
     */
    public function countUncompletedExercisesForUser(User $user)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('COUNT(DISTINCT e)')
            ->from($this->_entityName, 'e')
            ->join('e.participants', 'p')
            ->leftJoin('e.submissions', 's', Expr\Join::WITH, 's.exercise = e AND s.user = :user')
            ->where('e.startDate <= CURRENT_TIMESTAMP()')
            ->andWhere("CURRENT_TIMESTAMP() <= DATE_ADD(e.endDate, e.tolerance, 'day')")
            ->andWhere('p IN (:groups)')
            ->andWhere('s.id IS NULL')
            ->setParameter('user', $user)
            ->setParameter('groups', $user->getGroups()->toArray())
        ;

        return (integer)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Gets an array of all submission made by the given user for the exercise
     *
     * @todo FIXME: Move code as much as possible into DQL
     *
     * @param Exercise $exercise
     * @param User $user
     *
     * @return array
     */
    public function findUserSubmissions(Exercise $exercise, User $user = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('s, f')
            ->from('IServExerciseBundle:ExerciseSubmission', 's')
            ->join('s.file', 'f')
            ->where('s.exercise = :exercise AND s.user = :user')
            ->orderBy('f.title')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Exercise $exercise
     * @param User|null $user
     * @return ExerciseSubmission
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserTextSubmission(Exercise $exercise, User $user = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('s')
            ->from('IServExerciseBundle:ExerciseSubmission', 's')
            ->where('s.exercise = :exercise AND s.user = :user')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
        ;

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $submission = new ExerciseSubmission();
            $submission->setExercise($exercise);
            $submission->setUser($user);

            return $submission;
        }
    }

    /**
     * Find all exercises the user has access to
     *
     * @param User $user
     * @return array
     */
    public function findExercisesByUser(User $user)
    {
        $qb = $this->createQueryBuilder('e');

        $qb
            ->select('e, s')
            ->join('e.participants', 'p')
            ->leftJoin('e.submissions', 's', Expr\Join::WITH, 's.exercise = e AND s.user = :user')
            ->where('e.startDate <= CURRENT_TIMESTAMP()')
            ->andWhere("CURRENT_TIMESTAMP() <= DATE_ADD(e.endDate, e.tolerance, 'day')")
            ->andWhere('p IN (:groups)')
            ->orderBy('e.endDate')
            ->setParameter('user', $user)
            ->setParameter('groups', $user->getGroups()->toArray())
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all users that have no Submission
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function findUsersWithoutSubmissions(Exercise $exercise)
    {
        $mapPrivilege = function($priv) {
            if (substr($priv, 0, 5) == 'PRIV_') {
                $priv = strtolower(substr($priv, 5));
            }

            return $priv;
        };

        /* @var $userRepo \IServ\CoreBundle\Entity\UserRepository */
        $userRepo = $this->_em->getRepository('IServCoreBundle:User');
        // Query all users with groups in this exercise
        // Exclude all users with submission
        // Exclude users with exclusion priv
        $hasSubmQb = $this->_em->createQueryBuilder();
        $hasSubmQb
            ->select('IDENTITY(s.user)')
            ->from('IServExerciseBundle:ExerciseSubmission', 's')
            ->where('s.exercise = :exercise')
        ;

        $qb = $this->createQueryBuilder('e');

        $qb
            ->select('u.firstname', 'u.lastname', 'u.username')
            ->join('e.participants', 'g')
            ->join('g.users', 'u')
            ->where('e = :exercise')
            ->andWhere($qb->expr()->notIn('u', $hasSubmQb->getDQL()))
            ->andWhere($qb->expr()->notIn('u', $userRepo->createPrivilegeQueryBuilder()->getDQL()))
            ->orderBy('u.firstname, u.lastname')
            ->setParameter('exercise', $exercise)
            ->setParameter('priv', $mapPrivilege(Privilege::EXCLUDED_FROM_EXERCISES))
        ;

        return $qb->getQuery()->getResult();
    }

}
