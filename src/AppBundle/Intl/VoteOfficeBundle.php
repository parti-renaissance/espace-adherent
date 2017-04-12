<?php

namespace AppBundle\Intl;

class VoteOfficeBundle
{
    const VoteOffice = [
        'AF' => [ // AFGHANISTAN
            'KABOUL'
        ],
        'ZA' => [ // AFRIQUE DU SUD
            'JOHANNESBURG',
            'LE CAP',
        ],
        'AL' => [ // ALBANIE
            'TIRANA',
        ],
        'DZ' => [ // ALGERIE
            'ALGER',
            'ANNABA',
            'ORAN',
        ],
        'DE' => [ // ALLEMAGNE
            'BERLIN',
            'DUSSELDORF',
            'HAMBOURG',
            'FRANCFORT',
            'MUNICH',
            'SARREBRUCK',
            'STUTTGART',
        ],
        'ANCIENNE REPUBLIQUE YOUGOSLAVE DE MACEDOINE' => 'SKOPJE', // @TODO ???
        'AD' => [ // ANDORRE
            'ANDORRE',
        ],
        'AO' => [ // ANGOLA
            'LUANDA',
        ],
        'SA' => [ // ARABIE SAOUDITE
            'DJEDDAH',
            'RIYAD',
        ],
        'AR' => [ // ARGENTINE
            'BUENOS AIRES',
        ],
        'AM' => [ // ARMENIE
            'EREVAN',
        ],
        'AU' => [ // AUSTRALIE
            'SYDNEY',
        ],
        'AT' => [ // AUTRICHE
            'VIENNE',
        ],
        'AZ' => [ // AZERBAIDJAN
            'BAKOU',
        ],
        'BH' => [ // BAHREIN
            'MANAMA',
        ],
        'BD' => [ // BANGLADESH
            'DACCA',
        ],
        'BE' => [ // BELGIQUE
            'BRUXELLES',
        ],
        'BJ' => [ // BENIN
            'COTONOU',
        ],
        'BY' => [ // BIELORUSSIE
            'MINSK',
        ],
        'MM' => [ // BIRMANIE
            'RANGOUN',
        ],
        'BO' => [ // BOLIVIE
            'LA PAZ',
        ],
        'BA' => [ // BOSNIE-HERZEGOVINE
            'SARAJEVO',
        ],
        'BW' => [ // BOTSWANA
            'JOHANNESBOURG',
        ],
        'BR' => [ // BRESIL
            'BRASILIA',
            'RECIFE',
            'RIO DE JANEIRO',
            'SAO PAULO',
        ],
        'BN' => [ // BRUNEI
            'SINGAPOUR',
        ],
        'BG' => [ // BULGARIE
            'SOFIA',
        ],
        'BF' => [ // BURKINA FASO
            'OUAGADOUGOU',
        ],
        'BI' => [ // BURUNDI
            'BUJUMBURA',
        ],
        'KH' => [ // CAMBODGE
            'PHNOM PENH',
        ],
        'CM' => [ // CAMEROUN
            'DOUALA',
            'YAOUNDE',
        ],
        'CA' => [ // CANADA
            'MONCTON',
            'MONTREAL',
            'QUEBEC',
            'TORONTO',
            'VANCOUVER',
        ],
        'CV' => [ // CAP-VERT
            'DAKAR',
        ],
        'CF' => [ // CENTRAFRICAINE (République)
            'BANGUI',
        ],
        'CL' => [ // CHILI
            'SANTIAGO',
        ],
        'CN' => [ // CHINE
            'CANTON',
            'CHENGDU',
            'HONG KONG',
            'PEKIN',
            'SHANGHAI',
            'SHENYANG',
            'WUHAN',
        ],
        'CY' => [ // CHYPRE
            'NICOSIE',
        ],
        'CO' => [ // COLOMBIE
            'BOGOTA',
        ],
        'KM' => [ // COMORES
            'MORONI',
        ],
        'CG' => [ // CONGO
            'BRAZZAVILLE',
            'POINTE NOIRE',
        ],
        'CD' => [ // CONGO (République démocratique)
            'KINSHASA',
        ],
        'KR' => [ // COREE DU SUD
            'SEOUL',
        ],
        'CR' => [ // COSTA RICA
            'SAN JOSE',
        ],
        'CI' => [ // COTE D'IVOIRE
            'ABIDJAN',
        ],
        'HR' => [ // CROATIE
            'ZAGREB',
        ],
        'CU' => [ // CUBA
            'LA HAVANE',
        ],
        'DK' => [ // DANEMARK
            'COPENHAGUE',
        ],
        'DJ' => [ // DJIBOUTI
            'DJIBOUTI',
        ],
        'DO' => [ // DOMINICAINE (République)
            'SAINT-DOMINGUE',
        ],
        'EG' => [ // EGYPTE
            'ALEXANDRIE',
            'LE CAIRE',
        ],
        'SV' => [ // EL SALVADOR
            'GUATEMALA',
        ],
        'AE' => [ // EMIRATS ARABES UNIS
            'ABOU DABI',
            'DUBAI',
        ],
        'EC' => [ // EQUATEUR
            'QUITO',
        ],
        'ES' => [ // ESPAGNE
            'BARCELONE',
            'BILBAO',
            'MADRID',
            'SEVILLE',
        ],
        'EE' => [ // ESTONIE
            'TALLINN',
        ],
        'US' => [ // ETATS-UNIS D`AMERIQUE
            'ATLANTA',
            'BOSTON',
            'CHICAGO',
            'HOUSTON',
            'LA NOUVELLE-ORLEANS',
            'LOS ANGELES',
            'MIAMI',
            'NEW YORK',
            'SAN FRANCISCO',
            'WASHINGTON',
        ],
        'ET' => [ // ETHIOPIE
            'ADDIS-ABEBA',
        ],
        'FJ' => [ // FIDJI
            'SUVA',
        ],
        'FI' => [ // FINLANDE
            'HELSINKI',
        ],
        'GA' => [ // GABON
            'LIBREVILLE',
        ],
        'GE' => [ // GEORGIE
            'TBILISSI',
        ],
        'GH' => [ // GHANA
            'ACCRA',
        ],
        'GR' => [ // GRECE
            'ATHENES',
            'THESSALONIQUE',
        ],
        'GT' => [ // GUATEMALA
            'GUATEMALA',
        ],
        'GN' => [ // GUINEE
            'CONAKRY',
        ],
        'GW' => [ // GUINEE BISSAO
            'DAKAR',
        ],
        'GQ' => [ // GUINEE EQUATORIALE
            'MALABO',
        ],
        'HT' => [ // HAITI
            'PORT-AU-PRINCE',
        ],
        'HN' => [ // HONDURAS
            'GUATEMALA',
        ],
        'HU' => [ // HONGRIE
            'BUDAPEST',
        ],
        'IN' => [ // INDE
            'BANGALORE',
            'BOMBAY',
            'CALCUTTA',
            'NEW DELHI',
            'PONDICHERY',
        ],
        'ID' => [ // INDONESIE
            'JAKARTA',
        ],
        'IQ' => [ // IRAK
            'BAGDAD',
            'ERBIL',
        ],
        'IR' => [ // IRAN
            'TEHERAN',
        ],
        'IE' => [ // IRLANDE
            'DUBLIN',
        ],
        'IS' => [ // ISLANDE
            'REYKJAVIK',
        ],
        'IL' => [ // ISRAEL
            'HAIFA',
            'JERUSALEM',
            'TEL-AVIV',
        ],
        'IT' => [ // ITALIE
            'MILAN',
            'NAPLES',
            'ROME',
        ],
        'JM' => [ // JAMAÏQUE
            'KINGSTON',
        ],
        'JP' => [ // JAPON
            'KYOTO',
            'TOKYO',
        ],
        'JO' => [ // JORDANIE
            'AMMAN',
        ],
        'KZ' => [ // KAZAKHSTAN
            'ALMATY',
        ],
        'KE' => [ // KENYA
            'NAIROBI',
        ],
        'XK' => [ // KOSOVO
            'PRISTINA',
        ],
        'KW' => [ // KOWEIT
            'KOWEIT',
        ],
        'LA' => [ // LAOS
            'VIENTIANE',
        ],
        'LV' => [ // LETTONIE
            'RIGA',
        ],
        'LB' => [ // LIBAN
            'BEYROUTH',
        ],
        'LR' => [ // LIBERIA
            'ABIDJAN',
        ],
        'LY' => [ // LIBYE
            'TRIPOLI (délocalisé à Tunis)',
        ],
        'LT' => [ // LITUANIE
            'VILNIUS',
        ],
        'LU' => [ // LUXEMBOURG
            'LUXEMBOURG',
        ],
        'MG' => [ // MADAGASCAR
            'TANANARIVE',
        ],
        'MY' => [ // MALAISIE
            'KUALA LUMPUR',
        ],
        'ML' => [ // MALI
            'BAMAKO',
        ],
        'MT' => [ // MALTE
            'LA VALETTE',
        ],
        'MA' => [ // MAROC
            'AGADIR',
            'CASABLANCA',
            'FES',
            'MARRAKECH',
            'RABAT',
            'TANGER',
        ],
        'MU' => [ // MAURICE
            'PORT-LOUIS',
        ],
        'MR' => [ // MAURITANIE
            'NOUAKCHOTT',
        ],
        'MX' => [ // MEXIQUE
            'MEXICO',
        ],
        'MB' => [ // MOLDAVIE
            'BUCAREST',
        ],
        'MC' => [ // MONACO
            'MONACO',
        ],
        'MN' => [ // MONGOLIE
            'OULAN-BATOR',
        ],
        'ME' => [ // MONTENEGRO
            'TIRANA',
        ],
        'MZ' => [ // MOZAMBIQUE
            'MAPUTO',
        ],
        'NA' => [ // NAMIBIE
            'JOHANNESBOURG',
        ],
        'NP' => [ // NEPAL
            'NEW DELHI',
        ],
        'NI' => [ // NICARAGUA
            'SAN JOSE',
        ],
        'NE' => [ // NIGER
            'NIAMEY',
        ],
        'NG' => [ // NIGERIA
            'ABUJA',
            'LAGOS',
        ],
        'NO' => [ // NORVEGE
            'OSLO',
        ],
        'NZ' => [ // NOUVELLE-ZELANDE
            'WELLINGTON',
        ],
        'OM' => [ // OMAN
            'MASCATE',
        ],
        'UG' => [ // OUGANDA
            'KAMPALA',
        ],
        'UZ' => [ // OUZBEKISTAN
            'TACHKENT',
        ],
        'PK' => [ // PAKISTAN
            'ISLAMABAD',
        ],
        'KARACHI' => [ // KARACHI // @TODO ???
            'Karachi',
        ],
        'PA' => [ // PANAMA
            'PANAMA',
        ],
        'PG' => [ // PAPOUASIE-NOUVELLE-GUINEE
            'PORT MORESBY',
        ],
        'PY' => [ // PARAGUAY
            'BUENOS AIRES',
        ],
        'NL' => [ // PAYS-BAS
            'AMSTERDAM',
        ],
        'PE' => [ // PEROU
            'LIMA',
        ],
        'PH' => [ // PHILIPPINES
            'MANILLE',
        ],
        'PL' => [ // POLOGNE
            'CRACOVIE',
            'VARSOVIE',
        ],
        'PT' => [ // PORTUGAL
            'LISBONNE',
        ],
        'QA' => [ // QATAR
            'DOHA',
        ],
        'RO' => [ // ROUMANIE
            'BUCAREST',
        ],
        'GB' => [ // ROYAUME-UNI
            'EDIMBOURG',
            'LONDRES',
            'EKATERINBOURG',
            'MOSCOU',
            'SAINT-PETERSBOURG',
        ],
        'RW' => [ // RWANDA
            'KIGALI',
        ],
        'LC' => [ // SAINTE-LUCIE
            'CASTRIES',
        ],
        'SN' => [ // SENEGAL
            'DAKAR',
        ],
        'RS' => [ // SERBIE
            'BELGRADE',
        ],
        'SC' => [ // SEYCHELLES
            'VICTORIA',
        ],
        'SG' => [ // SINGAPOUR
            'SINGAPOUR',
        ],
        'SK' => [ // SLOVAQUIE
            'BRATISLAVA',
        ],
        'SI' => [ // SLOVENIE
            'LJUBLJANA',
        ],
        'SD' => [ // SOUDAN
            'KHARTOUM',
        ],
        'SS' => [ // SOUDAN DU SUD
            'ADDIS-ABEBA',
        ],
        'LK' => [ // SRI LANKA
            'COLOMBO',
        ],
        'SE' => [ // SUEDE
            'STOCKHOLM',
        ],
        'CH' => [ // SUISSE
            'GENEVE',
            'ZURICH',
        ],
        'SR' => [ // SURINAM
            'PARAMARIBO',
        ],
        'SY' => [ // SYRIE
            'DAMAS (délocalisé à Beyrouth)',
        ],
        'TW' => [ // TAIWAN
            'TAIPEI',
        ],
        'TZ' => [ // TANZANIE
            'DAR ES SALAM',
        ],
        'TD' => [ // TCHAD
            'N\'DJAMENA',
        ],
        'CZ' => [ // TCHEQUE (République)
            'PRAGUE',
        ],
        'TH' => [ // THAÏLANDE
            'BANGKOK',
        ],
        'TG' => [ // TOGO
            'LOME',
        ],
        'TT' => [ // TRINITE-ET-TOBAGO
            'PORT D\'ESPAGNE',
        ],
        'TN' => [ // TUNISIE
            'TUNIS',
        ],
        'TM' => [ // TURKMENISTAN
            'ACHGABAT',
        ],
        'TR' => [ // TURQUIE
            'ANKARA',
            'ISTANBUL',
        ],
        'UA' => [ // UKRAINE
            'KIEV',
        ],
        'UY' => [ // URUGUAY
            'MONTEVIDEO',
        ],
        'VU' => [ // VANUATU
            'PORT-VILA',
        ],
        'VE' => [ // VENEZUELA
            'CARACAS',
        ],
        'VN' => [ // VIETNAM
            'HANOI',
            'HO CHI MINH-VILLE',
        ],
        'YE' => [ // YEMEN
            'SANAA (délocalisé à Djibouti)',
        ],
        'ZM' => [ // ZAMBIE
            'JOHANNESBOURG',
        ],
        'ZW' => [ // ZIMBABWE
            'HARARE',
        ]
    ];
}
