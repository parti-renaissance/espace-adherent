<?php

namespace AppBundle\Entity\ElectedRepresentative;

use MyCLabs\Enum\Enum;

final class MandateTypeEnum extends Enum
{
    public const CITY_COUNCIL = 'conseiller_municipal';
    public const EPCI_MEMBER = 'membre_EPCI';
    public const DEPARTMENTAL_COUNCIL = 'conseiller_departemental';
    public const REGIONAL_COUNCIL = 'conseiller_regional';
    public const CORSICA_ASSEMBLY_MEMBER = 'membre_assemblee_corse';
    public const DEPUTY = 'depute';
    public const SENATOR = 'senateur';
    public const EURO_DEPUTY = 'euro_depute';

    public const CITY_COUNCIL_LABEL = 'Conseiller municipal';
    public const EPCI_MEMBER_LABEL = 'Membre d\'EPCI';
    public const DEPARTMENTAL_COUNCIL_LABEL = 'Conseiller départemental';
    public const REGIONAL_COUNCIL_LABEL = 'Conseiller régional';
    public const CORSICA_ASSEMBLY_MEMBER_LABEL = 'Membre de l\'Assemblée de Corse';
    public const DEPUTY_LABEL = 'Député';
    public const SENATOR_LABEL = 'Sénateur';
    public const EURO_DEPUTY_LABEL = 'Député européen';

    public const CHOICES = [
        self::CITY_COUNCIL_LABEL => self::CITY_COUNCIL,
        self::EPCI_MEMBER_LABEL => self::EPCI_MEMBER,
        self::DEPARTMENTAL_COUNCIL_LABEL => self::DEPARTMENTAL_COUNCIL,
        self::REGIONAL_COUNCIL_LABEL => self::REGIONAL_COUNCIL,
        self::CORSICA_ASSEMBLY_MEMBER_LABEL => self::CORSICA_ASSEMBLY_MEMBER,
        self::DEPUTY_LABEL => self::DEPUTY,
        self::SENATOR_LABEL => self::SENATOR,
        self::EURO_DEPUTY_LABEL => self::EURO_DEPUTY,
    ];
}
