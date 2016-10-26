<?php
namespace IServ\NachschreibarbeitenBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateRangeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value->getStartDate() > $value->getEndDate()) {
            $this->context->addViolation($constraint->getMessage());
        }
    }
}