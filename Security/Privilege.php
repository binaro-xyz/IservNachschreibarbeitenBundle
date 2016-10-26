<?php
// src/IServ/ExerciseBundle/Security/Privilege.php
namespace IServ\NachschreibarbeitenBundle\Security;

/**
 * Privilege container for Exercise
 */
final class Privilege
{
    /**
     * Users creating/modifying exercises
     */
    const ACCESS_NACHSCHREIBARBEITEN = 'PRIV_ACCESS_NACHSCHREIBARBEITEN';

    /**
     * Users excluded from exercises
     */
    const EXCLUDED_FROM_EXERCISES = 'PRIV_EXERCISES_EXCLUDE';

    /**
     * Groups flagged to do exercises
     */
    const FLAG_DOES_EXERCISES = 'exercise_allowed';
}
