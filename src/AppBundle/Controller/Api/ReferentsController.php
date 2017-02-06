<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class ReferentsController extends Controller
{
    /**
     * @Route("/referents", name="api_referents")
     * @Method("GET")
     */
    public function indexAction()
    {
        return new JsonResponse(self::$referents);
    }

    private static $referents = [
        [
            'postalCode' => '81000',
            'name' => 'Clement Baller',
            'country' => 'France',
            'coordinates' => [2.148, 43.9298],
        ],
        [
            'postalCode' => '75005, 75006',
            'name' => 'Gilles Le Gendre',
            'country' => 'France',
            'coordinates' => [2.344212, 48.846246],
        ],
        [
            'postalCode' => '75012',
            'name' => 'Pierre-Alain Raphan',
            'country' => 'France',
            'coordinates' => [2.3876, 48.8412],
        ],
        [
            'postalCode' => '75016',
            'name' => 'Agnés Pannier',
            'country' => 'France',
            'coordinates' => [2.2769, 48.8637],
        ],
        [
            'postalCode' => '52000',
            'name' => 'Aude Crombé',
            'country' => 'France',
            'coordinates' => [5.1874, 48.1469],
        ],
        [
            'postalCode' => '28000',
            'name' => 'Aurelie Musset',
            'country' => 'France',
            'coordinates' => [1.4892, 48.4469],
        ],
        [
            'postalCode' => '66000',
            'name' => 'Sebastien Cazenove',
            'country' => 'France',
            'coordinates' => [2.8954, 42.6976],
        ],
        [
            'postalCode' => '35000',
            'name' => 'Florian Bachelier',
            'country' => 'France',
            'coordinates' => [-1.6743, 48.112],
        ],
        [
            'postalCode' => '90000',
            'name' => 'Bruno Kern',
            'country' => 'France',
            'coordinates' => [6.8667, 47.6333],
        ],
        [
            'postalCode' => '973000',
            'name' => 'Jonas Océan',
            'country' => 'France',
            'coordinates' => [-52.325249, 4.941459],
        ],
        [
            'postalCode' => '54000',
            'name' => 'Carole Grandjean',
            'country' => 'France',
            'coordinates' => [6.185, 48.6844],
        ],
        [
            'postalCode' => '62000',
            'name' => 'Coralie Rembert',
            'country' => 'France',
            'coordinates' => [2.7819, 50.293],
        ],
        [
            'postalCode' => '12000',
            'name' => 'Thomas Mogharaei',
            'country' => 'France',
            'coordinates' => [2.5734, 44.3526],
        ],
        [
            'postalCode' => '14000',
            'name' => 'Elisabeth Mailloux Jaskulke',
            'country' => 'France',
            'coordinates' => [-0.3591, 49.1859],
        ],
        [
            'postalCode' => '79000',
            'name' => 'Johan Baufreton',
            'country' => 'France',
            'coordinates' => [-0.5129, 46.3021],
        ],
        [
            'postalCode' => '17000',
            'name' => 'Isabelle Vetois',
            'country' => 'France',
            'coordinates' => [-1.15, 46.1667],
        ],
        [
            'postalCode' => '75017',
            'name' => 'Laurent Saint-Martin',
            'country' => 'France',
            'coordinates' => [2.3219, 48.8835],
        ],
        [
            'postalCode' => '60000',
            'name' => 'Dominique Lelong',
            'country' => 'France',
            'coordinates' => [2.0833, 49.4333],
        ],
        [
            'postalCode' => '43000',
            'name' => 'Pierre Eteocle',
            'country' => 'France',
            'coordinates' => [3.8603, 45.0709],
        ],
        [
            'postalCode' => '83000',
            'name' => 'Valerie Longchampt',
            'country' => 'France',
            'coordinates' => [5.9333, 43.1167],
        ],
        [
            'postalCode' => '75011',
            'name' => 'Marianna Mendza',
            'country' => 'France',
            'coordinates' => [2.3795, 48.8574],
        ],
        [
            'postalCode' => '47000',
            'name' => 'Olivier Damaisin',
            'country' => 'France',
            'coordinates' => [0.616995, 44.202338],
        ],
        [
            'postalCode' => '40000',
            'name' => 'Helne Verny',
            'country' => 'France',
            'coordinates' => [-0.4971, 43.8902],
        ],
        [
            'postalCode' => '75009, 75010',
            'name' => 'Celine Calvez',
            'country' => 'France',
            'coordinates' => [2.348584, 48.870625],
        ],
        [
            'postalCode' => '19000',
            'name' => 'Patricia Bordas',
            'country' => 'France',
            'coordinates' => [1.8008, 45.307],
        ],
        [
            'postalCode' => '75018',
            'name' => 'Justine Henry',
            'country' => 'France',
            'coordinates' => [2.3444, 48.8925],
        ],
        [
            'postalCode' => '24000',
            'name' => 'Michel Delpon',
            'country' => 'France',
            'coordinates' => [0.7167, 45.1833],
        ],
        [
            'postalCode' => '64000',
            'name' => 'Nathalie Niel',
            'country' => 'France',
            'coordinates' => [-0.3667, 43.3],
        ],
        [
            'postalCode' => '59000',
            'name' => 'Christophe Itier',
            'country' => 'France',
            'coordinates' => [3.0586, 50.633],
        ],
        [
            'postalCode' => '26000',
            'name' => 'Michel Gregoire',
            'country' => 'France',
            'coordinates' => [4.9, 44.9333],
        ],
        [
            'postalCode' => '60000',
            'name' => 'Richard Perrin',
            'country' => 'France',
            'coordinates' => [7.263545, 43.702555],
        ],
        [
            'postalCode' => '30000',
            'name' => 'Jerôme Talon',
            'country' => 'France',
            'coordinates' => [4.35, 43.8333],
        ],
        [
            'postalCode' => '27000',
            'name' => 'Fabien Gouttefarde',
            'country' => 'France',
            'coordinates' => [1.1508, 49.0241],
        ],
        [
            'postalCode' => '61000',
            'name' => 'Arnaud Bellet',
            'country' => 'France',
            'coordinates' => [0.1333, 48.45],
        ],
        [
            'postalCode' => '32000',
            'name' => 'Sylvie Theye',
            'country' => 'France',
            'coordinates' => [0.5833, 43.65],
        ],
        [
            'postalCode' => '47000',
            'name' => 'Sebastien Maurel',
            'country' => 'France',
            'coordinates' => [1.435434, 44.446095],
        ],
        [
            'postalCode' => '88000',
            'name' => 'Serge Vincent',
            'country' => 'France',
            'coordinates' => [6.5153, 48.2001],
        ],
        [
            'postalCode' => '75007, 75008',
            'name' => 'Veronique Tommasi',
            'country' => 'France',
            'coordinates' => [2.321, 48.8565],
        ],
        [
            'postalCode' => '40000',
            'name' => 'Caroline Breque',
            'country' => 'France',
            'coordinates' => [-0.4971, 43.8902],
        ],
        [
            'postalCode' => '50000',
            'name' => 'Bernard Renoy',
            'country' => 'France',
            'coordinates' => [-1.0725, 49.0894],
        ],
        [
            'postalCode' => '25000',
            'name' => 'Alexandra Cordier',
            'country' => 'France',
            'coordinates' => [6.0182, 47.2488],
        ],
        [
            'postalCode' => '45000',
            'name' => 'Emmanuel Constantin',
            'country' => 'France',
            'coordinates' => [1.9039, 47.9029],
        ],
        [
            'postalCode' => '86000',
            'name' => 'Philippe Chadeyron',
            'country' => 'France',
            'coordinates' => [0.3333, 46.5833],
        ],
        [
            'postalCode' => '94000',
            'name' => 'Laetitia Avia',
            'country' => 'France',
            'coordinates' => [2.4667, 48.7833],
        ],
        [
            'postalCode' => '69001',
            'name' => 'Bruno Bonnell',
            'country' => 'France',
            'coordinates' => [4.8345, 45.7676],
        ],
        [
            'postalCode' => '68000',
            'name' => 'Laurent Bernhard',
            'country' => 'France',
            'coordinates' => [7.3667, 48.0833],
        ],
        [
            'postalCode' => '20000',
            'name' => 'Cyril Thirion',
            'country' => 'France',
            'coordinates' => [3.621456, 49.563379],
        ],
        [
            'postalCode' => '16000',
            'name' => 'Thomas Mesnier',
            'country' => 'France',
            'coordinates' => [0.15, 45.65],
        ],
        [
            'postalCode' => '78000',
            'name' => 'Aziz-François Ndiaye',
            'country' => 'France',
            'coordinates' => [2.1333, 48.8],
        ],
        [
            'postalCode' => '11000',
            'name' => 'Marie-Héléne Regnier',
            'country' => 'France',
            'coordinates' => [2.35, 43.2167],
        ],
        [
            'postalCode' => '21000',
            'name' => 'Danielle Juban',
            'country' => 'France',
            'coordinates' => [5.0167, 47.3167],
        ],
        [
            'postalCode' => '75020',
            'name' => 'Anne-Lise Chagneau',
            'country' => 'France',
            'coordinates' => [2.3984, 48.8646],
        ],
        [
            'postalCode' => '20000',
            'name' => 'André de Cafarelli',
            'country' => 'France',
            'coordinates' => [8.737346, 41.926178],
        ],
        [
            'postalCode' => '58000',
            'name' => 'Nicolas Loisy',
            'country' => 'France',
            'coordinates' => [3.1833, 46.95],
        ],
        [
            'postalCode' => '75019',
            'name' => 'Vitaly Goloubev',
            'country' => 'France',
            'coordinates' => [2.3822, 48.8817],
        ],
        [
            'postalCode' => '37000',
            'name' => 'Philippe Chalumeau',
            'country' => 'France',
            'coordinates' => [0.6833, 47.3833],
        ],
        [
            'postalCode' => '41000',
            'name' => 'Christine Jagueneau',
            'country' => 'France',
            'coordinates' => [1.2667, 47.6],
        ],
        [
            'postalCode' => '75013',
            'name' => 'Philippe Zaouati',
            'country' => 'France',
            'coordinates' => [2.3561, 48.8322],
        ],
        [
            'postalCode' => '34000',
            'name' => 'Coralie Dubost',
            'country' => 'France',
            'coordinates' => [3.8772, 43.6109],
        ],
        [
            'postalCode' => '87000',
            'name' => 'Stephane Bobin',
            'country' => 'France',
            'coordinates' => [1.2578, 45.8315],
        ],
        [
            'postalCode' => '91000',
            'name' => 'Laetitia Romeiro',
            'country' => 'France',
            'coordinates' => [2.45, 48.6333],
        ],
        [
            'postalCode' => '74000',
            'name' => 'Guillaume Gibouin',
            'country' => 'France',
            'coordinates' => [6.1167, 45.9],
        ],
        [
            'postalCode' => '38000',
            'name' => 'Didier Rambaud',
            'country' => 'France',
            'coordinates' => [5.7167, 45.1667],
        ],
        [
            'postalCode' => '77000',
            'name' => 'Stephanie Do',
            'country' => 'France',
            'coordinates' => [2.6636, 48.5088],
        ],
        [
            'postalCode' => '75001, 75002, 73003, 75004',
            'name' => 'Isabelle de Courtivron',
            'country' => 'France',
            'coordinates' => [2.352391, 48.860521],
        ],
        [
            'postalCode' => '29000',
            'name' => 'Pierre Karleskind',
            'country' => 'France',
            'coordinates' => [-4.099999, 48],
        ],
        [
            'postalCode' => '22000',
            'name' => 'Eric Bothorel',
            'country' => 'France',
            'coordinates' => [-2.7684, 48.5151],
        ],
        [
            'postalCode' => '80000',
            'name' => 'Laurence David-Moalic',
            'country' => 'France',
            'coordinates' => [2.3, 49.9],
        ],
        [
            'postalCode' => '39000',
            'name' => 'Philippe Antoine',
            'country' => 'France',
            'coordinates' => [5.55, 46.6667],
        ],
        [
            'postalCode' => '97400',
            'name' => 'Carine Garcia',
            'country' => 'France',
            'coordinates' => [55.386398, -21.130311],
        ],
        [
            'postalCode' => '33000',
            'name' => 'Tanguy Bernard',
            'country' => 'France',
            'coordinates' => [-0.5805, 44.8404],
        ],
        [
            'postalCode' => '44000',
            'name' => 'Valerie Oppelt',
            'country' => 'France',
            'coordinates' => [-1.5534, 47.2173],
        ],
        [
            'postalCode' => '72000',
            'name' => 'Marlène Schiappa',
            'country' => 'France',
            'coordinates' => [0.2, 48],
        ],
        [
            'postalCode' => '13001',
            'name' => 'Corinne Versini',
            'country' => 'France',
            'coordinates' => [5.3841, 43.2981],
        ],
        [
            'postalCode' => '92000',
            'name' => 'Laurianne Rossi',
            'country' => 'France',
            'coordinates' => [2.2067, 48.892],
        ],
        [
            'postalCode' => '73000',
            'name' => 'Jean-Christophe Masseron',
            'country' => 'France',
            'coordinates' => [5.9435, 45.585],
        ],
        [
            'postalCode' => '63000',
            'name' => 'Guy Lavocat',
            'country' => 'France',
            'coordinates' => [3.0863, 45.7797],
        ],
        [
            'postalCode' => '93000',
            'name' => 'Raïs Boutheina',
            'country' => 'France',
            'coordinates' => [2.45, 48.9],
        ],
        [
            'postalCode' => '85000',
            'name' => 'Stephane Buchou',
            'country' => 'France',
            'coordinates' => [-1.4546, 46.719],
        ],
        [
            'postalCode' => '51000',
            'name' => 'Sophie Barre',
            'country' => 'France',
            'coordinates' => [4.3672, 48.9539],
        ],
        [
            'postalCode' => '75014',
            'name' => 'Alexandre Kimmerlé',
            'country' => 'France',
            'coordinates' => [2.3302196, 48.8335771],
        ],
        [
            'postalCode' => '57000',
            'name' => 'Beatrice Agamennone',
            'country' => 'France',
            'coordinates' => [6.1727, 49.1191],
        ],
        [
            'postalCode' => '67000',
            'name' => 'Bruno Studer',
            'country' => 'France',
            'coordinates' => [7.743, 48.5834],
        ],
        [
            'postalCode' => '75015',
            'name' => 'Fanta Berete',
            'country' => 'France',
            'coordinates' => [2.3003, 48.8412],
        ],
        [
            'postalCode' => '1000',
            'name' => 'Olga Givernet',
            'country' => 'France',
            'coordinates' => [5.226123, 46.202233],
        ],
        [
            'postalCode' => '70000',
            'name' => 'Loic Biver',
            'country' => 'France',
            'coordinates' => [6.045, 47.6018],
        ],
        [
            'postalCode' => '04000',
            'name' => 'Héléne Verny',
            'country' => 'France',
            'coordinates' => [6.233991, 44.088886],
        ],
        [
            'postalCode' => '05000',
            'name' => 'Bernard Renoy',
            'country' => 'France',
            'coordinates' => [6.08677, 44.564546],
        ],
        [
            'postalCode' => '36000',
            'name' => 'Pascal Maitre',
            'country' => 'France',
            'coordinates' => [1.692031, 46.802439],
        ],
        [
            'postalCode' => '56000',
            'name' => 'Jean-Michel Jacques',
            'country' => 'France',
            'coordinates' => [-2.758, 47.6575],
        ],
        [
            'postalCode' => '49000',
            'name' => 'Michel Bodet',
            'country' => 'France',
            'coordinates' => [-0.590805, 47.463141],
        ],
        [
            'postalCode' => '08000',
            'name' => 'Sylvie Roguin',
            'country' => 'France',
            'coordinates' => [4.700075, 49.768905],
        ],
        [
            'postalCode' => '76000',
            'name' => 'Damien Adam',
            'country' => 'France',
            'coordinates' => [1.0215152, 49.4412454],
        ],
    ];
}
