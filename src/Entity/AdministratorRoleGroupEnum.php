<?php

namespace App\Entity;

enum AdministratorRoleGroupEnum: string
{
    case RE_SITE_WEB = 're_site_web';
    case COMMUNICATION = 'communication';
    case POLITIQUE = 'politique';
    case ADHERENTS = 'adherents';
    case TERRITOIRES = 'territoires';
    case APPLICATION_MOBILE = 'application_mobile';
    case PHONING = 'phoning';
    case PORTE_A_PORTE = 'porte_a_porte';
    case INSTANCES = 'instances';
    case FINANCES = 'finances';
    case TECH = 'tech';
    case IDEES = 'idees';
    case FORMATION = 'formation';
    case SOCLE_PROGRAMMATIQUE = 'socle_programmatique';
    case COMMUNAUTES_THEMATIQUES = 'communautes_thematiques';
    case ELECTIONS_DEPARTEMENTALES = 'elections_departementales';
    case ARCHIVES = 'archives';
}
