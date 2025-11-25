<?php

namespace App\Entity;

enum AdministratorRoleGroupEnum: string
{
    case ADHERENTS = 'adherents';
    case COMMUNICATION = 'communication';
    case EVENTS = 'events';
    case TERRITOIRES = 'territoires';
    case MOBILISATION = 'mobilisation';
    case ELUS = 'elus';
    case ELECTIONS = 'elections';
    case ELECTIONS_INTERNES = 'elections_internes';
    case APPLICATION_MOBILE = 'application_mobile';
    case FINANCES = 'finances';
    case TECH = 'tech';
    case IDEES = 'idees';
    case FORMATION = 'formation';
    case ARCHIVES_A_GARDER = 'archives_a_garder';
    case ARCHIVES_A_DEPUBLIER = 'archives_a_depublier';
    case ARCHIVES = 'archives';
    case COMMUNAUTES_THEMATIQUES = 'communautes_thematiques';
    case PETITION = 'petition';

    // Legacy
    case RE_SITE_WEB = 're_site_web';
    case POLITIQUE = 'politique';
    case PHONING = 'phoning';
    case PORTE_A_PORTE = 'porte_a_porte';
    case INSTANCES = 'instances';
    case ELECTIONS_DEPARTEMENTALES = 'elections_departementales';
}
