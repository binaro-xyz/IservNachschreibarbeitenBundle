<?php
namespace binaro\NachschreibarbeitenBundle\Security;

/**
 * Privilege container for Exercise
 */
final class Privilege
{
    /**
     * Users creating/modifying exercises
     */
    const ACCESS_NACHSCHREIBARBEITEN = 'PRIV_MOD_NACHSCHREIBARBEITEN_ACCESS';

    /**
     * Users excluded from exercises
     */
    const ADMIN_NACHSCHREIBARBEITEN = 'PRIV_MOD_NACHSCHREIBARBEITEN_ADMIN';
}
