<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class DataSurveyConstraint extends Constraint
{
    public $agreedToStayInContactRequired = 'data.survey.agreed_to_stay_in_contact.required';

    public $emailAddressRequired = 'data.survey.email_address.required';

    public $genderChoiceOtherNotSelectedMessage = 'data.survey.gender_choice_other.not_selected';
    public $genderOtherEmptyMessage = 'data.survey.gender_other.empty';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
