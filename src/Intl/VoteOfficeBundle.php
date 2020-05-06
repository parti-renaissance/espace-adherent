<?php

namespace App\Intl;

class VoteOfficeBundle
{
    const VOTE_OFFICES = [
        'AF' => [ // AFGHANISTAN
            'Kaboul',
        ],
        'ZA' => [ // AFRIQUE DU SUD
            'Johannesburg',
            'Le Cap',
        ],
        'AL' => [ // ALBANIE
            'Tirana',
        ],
        'DZ' => [ // ALGERIE
            'Alger',
            'Annaba',
            'Oran',
        ],
        'DE' => [ // ALLEMAGNE
            'Berlin',
            'Dusseldorf',
            'Hambourg',
            'Francfort',
            'Munich',
            'Nuremberg',
            'Sarrebruck',
            'Stuttgart',
        ],
        'MK' => [ // ANCIENNE REPUBLIQUE YOUGOSLAVE DE MACEDOINE
            'Skopje',
        ],
        'AD' => [ // ANDORRE
            'Andorre-la-Vieille',
            'Pas-de-la-Case',
        ],
        'AO' => [ // ANGOLA
            'Luanda',
        ],
        'SA' => [ // ARABIE SAOUDITE
            'Djeddah',
            'Riyad',
        ],
        'AR' => [ // ARGENTINE
            'Buenos Aires',
        ],
        'AM' => [ // ARMENIE
            'Erevan',
        ],
        'AU' => [ // AUSTRALIE
            'Sydney',
        ],
        'AT' => [ // AUTRICHE
            'Vienne',
        ],
        'AZ' => [ // AZERBAIDJAN
            'Bakou',
        ],
        'BH' => [ // BAHREIN
            'Manama',
        ],
        'BD' => [ // BANGLADESH
            'Dacca',
        ],
        'BE' => [ // BELGIQUE
            'Bruxelles',
        ],
        'BJ' => [ // BENIN
            'Cotonou',
        ],
        'BY' => [ // BIELORUSSIE
            'Minsk',
        ],
        'MM' => [ // BIRMANIE
            'Rangoun',
        ],
        'BO' => [ // BOLIVIE
            'La paz',
        ],
        'BA' => [ // BOSNIE-HERZEGOVINE
            'Sarajevo',
        ],
        'BW' => [ // BOTSWANA
            'Johannesbourg',
        ],
        'BR' => [ // BRESIL
            'Brasilia',
            'Recife',
            'Rio de janeiro',
            'Sao paulo',
        ],
        'BN' => [ // BRUNEI
            'Singapour',
        ],
        'BG' => [ // BULGARIE
            'Sofia',
        ],
        'BF' => [ // BURKINA FASO
            'Ouagadougou',
        ],
        'BI' => [ // BURUNDI
            'Bujumbura',
        ],
        'KH' => [ // CAMBODGE
            'Phnom penh',
        ],
        'CM' => [ // CAMEROUN
            'Douala',
            'Yaounde',
        ],
        'CA' => [ // CANADA
            'Moncton',
            'Montreal',
            'Quebec',
            'Toronto',
            'Vancouver',
        ],
        'CV' => [ // CAP-VERT
            'Dakar',
        ],
        'CF' => [ // CENTRAFRICAINE (République)
            'Bangui',
        ],
        'CL' => [ // CHILI
            'Santiago',
        ],
        'CN' => [ // CHINE
            'Canton',
            'Chengdu',
            'Hong Kong',
            'Pekin',
            'Shanghai',
            'Shenyang',
            'Wuhan',
        ],
        'CY' => [ // CHYPRE
            'Nicosie',
        ],
        'CO' => [ // COLOMBIE
            'Bogota',
        ],
        'KM' => [ // COMORES
            'Moroni',
        ],
        'CG' => [ // CONGO
            'Brazzaville',
            'Pointe-Noire',
        ],
        'CD' => [ // CONGO (République démocratique)
            'Kinshasa',
        ],
        'KR' => [ // COREE DU SUD
            'Seoul',
        ],
        'CR' => [ // COSTA RICA
            'San Jose',
        ],
        'CI' => [ // COTE D'IVOIRE
            'Abidjan',
        ],
        'HR' => [ // CROATIE
            'Zagreb',
        ],
        'CU' => [ // CUBA
            'La Havane',
        ],
        'DK' => [ // DANEMARK
            'Copenhague',
        ],
        'DJ' => [ // DJIBOUTI
            'Djibouti',
        ],
        'DO' => [ // DOMINICAINE (République)
            'Saint-Domingue',
        ],
        'EG' => [ // EGYPTE
            'Alexandrie',
            'Le Caire',
        ],
        'SV' => [ // EL SALVADOR
            'Guatemala',
        ],
        'AE' => [ // EMIRATS ARABES UNIS
            'Abou Dabi',
            'Dubai',
        ],
        'EC' => [ // EQUATEUR
            'Quito',
        ],
        'ES' => [ // ESPAGNE
            'Alicante',
            'Barcelone',
            'Bilbao',
            'Figueres',
            'Gerone',
            'Grenade',
            'Ibiza',
            'Las Palmas',
            'Madrid',
            'Malaga',
            'Murcie',
            'Palma de Majorque',
            'Saragosse',
            'Seville',
            'Tarragone-Reus',
            'Tenerife',
            'Valence',
            'Vigo',
        ],
        'EE' => [ // ESTONIE
            'Tallinn',
        ],
        'US' => [ // ETATS-UNIS D`AMERIQUE
            'Atlanta',
            'Boston',
            'Chicago',
            'Houston',
            'La Nouvelle-Orleans',
            'Los Angeles',
            'Miami',
            'New York',
            'San Francisco',
            'Washington',
        ],
        'ET' => [ // ETHIOPIE
            'Addis-Abeba',
        ],
        'FJ' => [ // FIDJI
            'Suva',
        ],
        'FI' => [ // FINLANDE
            'Helsinki',
        ],
        'GA' => [ // GABON
            'Libreville',
        ],
        'GE' => [ // GEORGIE
            'Tbilissi',
        ],
        'GH' => [ // GHANA
            'Accra',
        ],
        'GR' => [ // GRECE
            'Athenes',
            'Thessalonique',
        ],
        'GT' => [ // GUATEMALA
            'Guatemala',
        ],
        'GN' => [ // GUINEE
            'Conakry',
        ],
        'GW' => [ // GUINEE BISSAO
            'Dakar',
        ],
        'GQ' => [ // GUINEE EQUATORIALE
            'Malabo',
        ],
        'HT' => [ // HAITI
            'Port-au-Prince',
        ],
        'HN' => [ // HONDURAS
            'Guatemala',
        ],
        'HU' => [ // HONGRIE
            'Budapest',
        ],
        'IN' => [ // INDE
            'Bangalore',
            'Bombay',
            'Calcutta',
            'New Delhi',
            'Pondichery',
        ],
        'ID' => [ // INDONESIE
            'Jakarta',
        ],
        'IQ' => [ // IRAK
            'Bagdad',
            'Erbil',
        ],
        'IR' => [ // IRAN
            'Teheran',
        ],
        'IE' => [ // IRLANDE
            'Dublin',
        ],
        'IS' => [ // ISLANDE
            'Reykjavik',
        ],
        'IL' => [ // ISRAEL
            'Haifa',
            'Jerusalem',
            'Tel-Aviv',
        ],
        'IT' => [ // ITALIE
            'Milan',
            'Naples',
            'Rome',
        ],
        'JM' => [ // JAMAÏQUE
            'Kingston',
        ],
        'JP' => [ // JAPON
            'Kyoto',
            'Tokyo',
        ],
        'JO' => [ // JORDANIE
            'Amman',
        ],
        'KZ' => [ // KAZAKHSTAN
            'Almaty',
        ],
        'KE' => [ // KENYA
            'Nairobi',
        ],
        'XK' => [ // KOSOVO
            'Pristina',
        ],
        'KW' => [ // KOWEIT
            'Koweit',
        ],
        'LA' => [ // LAOS
            'Vientiane',
        ],
        'LV' => [ // LETTONIE
            'Riga',
        ],
        'LB' => [ // LIBAN
            'Beyrouth',
        ],
        'LR' => [ // LIBERIA
            'Abidjan',
        ],
        'LY' => [ // LIBYE
            'Tripoli (délocalisé à Tunis)',
        ],
        'LT' => [ // LITUANIE
            'Vilnius',
        ],
        'LU' => [ // LUXEMBOURG
            'Luxembourg',
        ],
        'MG' => [ // MADAGASCAR
            'Tananarive',
        ],
        'MY' => [ // MALAISIE
            'Kuala Lumpur',
        ],
        'ML' => [ // MALI
            'Bamako',
        ],
        'MT' => [ // MALTE
            'La Valette',
        ],
        'MA' => [ // MAROC
            'Agadir',
            'Casablanca',
            'Fes',
            'Marrakech',
            'Rabat',
            'Tanger',
        ],
        'MU' => [ // MAURICE
            'Port-Louis',
        ],
        'MR' => [ // MAURITANIE
            'Nouakchott',
        ],
        'MX' => [ // MEXIQUE
            'Mexico',
        ],
        'MB' => [ // MOLDAVIE
            'Bucarest',
        ],
        'MC' => [ // MONACO
            'Monaco',
        ],
        'MN' => [ // MONGOLIE
            'Oulan-Bator',
        ],
        'ME' => [ // MONTENEGRO
            'Tirana',
        ],
        'MZ' => [ // MOZAMBIQUE
            'Maputo',
        ],
        'NA' => [ // NAMIBIE
            'Johannesbourg',
        ],
        'NP' => [ // NEPAL
            'New Delhi',
        ],
        'NI' => [ // NICARAGUA
            'San Jose',
        ],
        'NE' => [ // NIGER
            'Niamey',
        ],
        'NG' => [ // NIGERIA
            'Abuja',
            'Lagos',
        ],
        'NO' => [ // NORVEGE
            'Oslo',
        ],
        'NZ' => [ // NOUVELLE-ZELANDE
            'Wellington',
        ],
        'OM' => [ // OMAN
            'Mascate',
        ],
        'UG' => [ // OUGANDA
            'Kampala',
        ],
        'UZ' => [ // OUZBEKISTAN
            'Tachkent',
        ],
        'PK' => [ // PAKISTAN
            'Islamabad',
            'Karachi',
        ],
        'PA' => [ // PANAMA
            'Panama',
        ],
        'PG' => [ // PAPOUASIE-NOUVELLE-GUINEE
            'Port Moresby',
        ],
        'PY' => [ // PARAGUAY
            'Buenos Aires',
        ],
        'NL' => [ // PAYS-BAS
            'Amsterdam',
        ],
        'PE' => [ // PEROU
            'Lima',
        ],
        'PH' => [ // PHILIPPINES
            'Manille',
        ],
        'PL' => [ // POLOGNE
            'Cracovie',
            'Varsovie',
        ],
        'PT' => [ // PORTUGAL
            'Faro',
            'Lisbonne',
            'Porto',
        ],
        'QA' => [ // QATAR
            'Doha',
        ],
        'RO' => [ // ROUMANIE
            'Bucarest',
        ],
        'GB' => [ // ROYAUME-UNI
            'Edimbourg',
            'Londres',
        ],
        'RU' => [ // RUSSIE
            'Ekaterinbourg',
            'Moscou',
            'Saint-Petersbourg',
        ],
        'RW' => [ // RWANDA
            'Kigali',
        ],
        'LC' => [ // SAINTE-LUCIE
            'Castries',
        ],
        'SN' => [ // SENEGAL
            'Dakar',
        ],
        'RS' => [ // SERBIE
            'Belgrade',
        ],
        'SC' => [ // SEYCHELLES
            'Victoria',
        ],
        'SG' => [ // SINGAPOUR
            'Singapour',
        ],
        'SK' => [ // SLOVAQUIE
            'Bratislava',
        ],
        'SI' => [ // SLOVENIE
            'Ljubljana',
        ],
        'SD' => [ // SOUDAN
            'Khartoum',
        ],
        'SS' => [ // SOUDAN DU SUD
            'Addis-Abeba',
        ],
        'LK' => [ // SRI LANKA
            'Colombo',
        ],
        'SE' => [ // SUEDE
            'Göteborg',
            'Stockholm',
        ],
        'CH' => [ // SUISSE
            'Geneve',
            'Zurich',
        ],
        'SR' => [ // SURINAM
            'Paramaribo',
        ],
        'SY' => [ // SYRIE
            'Damas (délocalisé à Beyrouth)',
        ],
        'TW' => [ // TAIWAN
            'Taipei',
        ],
        'TZ' => [ // TANZANIE
            'Dar es Salam',
        ],
        'TD' => [ // TCHAD
            'N\'Djamena',
        ],
        'CZ' => [ // TCHEQUE (République)
            'Prague',
        ],
        'TH' => [ // THAÏLANDE
            'Bangkok',
        ],
        'TG' => [ // TOGO
            'Lome',
        ],
        'TT' => [ // TRINITE-ET-TOBAGO
            'Port d\'Espagne',
        ],
        'TN' => [ // TUNISIE
            'Tunis',
        ],
        'TM' => [ // TURKMENISTAN
            'Achgabat',
        ],
        'TR' => [ // TURQUIE
            'Ankara',
            'Istanbul',
        ],
        'UA' => [ // UKRAINE
            'Kiev',
        ],
        'UY' => [ // URUGUAY
            'Montevideo',
        ],
        'VU' => [ // VANUATU
            'Port-Vila',
        ],
        'VE' => [ // VENEZUELA
            'Caracas',
        ],
        'VN' => [ // VIETNAM
            'Hanoi',
            'Ho Chi Minh-Ville',
        ],
        'YE' => [ // YEMEN
            'Sanaa (délocalisé à Djibouti)',
        ],
        'ZM' => [ // ZAMBIE
            'Johannesbourg',
        ],
        'ZW' => [ // ZIMBABWE
            'Harare',
        ],
    ];

    public static function getVoteOfficies(string $countryCode): array
    {
        return self::VOTE_OFFICES[$countryCode] ?? [];
    }
}
