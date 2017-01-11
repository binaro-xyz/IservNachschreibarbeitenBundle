<?php
namespace binaro\NachschreibarbeitenBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateRange extends Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'iserv_exercise_daterange_validator';
    }

    public function getMessage()
    {
        return _('The deadline must be past the start date.');
    }

}
