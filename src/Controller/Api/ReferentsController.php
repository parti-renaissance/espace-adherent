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
     * @Route("/referents", defaults={"_enable_campaign_silence"=true}, name="api_referents")
     * @Method("GET")
     */
    public function indexAction()
    {
        return new JsonResponse(self::$referents);
    }

    private static $referents = [
        [
            'postalCode' => '65000',
            'name' => 'Frédéric Laval',
            'coordinates' => [0.070888, 43.228758],
        ],
        [
            'postalCode' => '42000',
            'name' => 'Christian Soleil',
            'coordinates' => [4.397309, 45.435549],
        ],
        [
            'postalCode' => '18000',
            'name' => 'Loïc Kervran',
            'coordinates' => [2.37736, 47.083994],
        ],
        [
            'postalCode' => '10000',
            'name' => 'Fabien Lauro',
            'coordinates' => [4.076463, 48.300452],
        ],
        [
            'postalCode' => '89000',
            'name' => 'Patrice José Tampied Azurza',
            'coordinates' => [3.569805, 47.79466],
        ],
        [
            'postalCode' => '84000',
            'name' => 'Jean François Césarini',
            'coordinates' => [4.838738, 43.933313],
        ],
        [
            'postalCode' => '53000',
            'name' => 'Aurélien Page',
            'coordinates' => [-0.766356, 48.066823],
        ],
        [
            'postalCode' => '50000',
            'name' => 'Pierre Henry Debray',
            'coordinates' => [-1.093256, 49.112172],
        ],
        [
            'postalCode' => '38000',
            'name' => 'Francis Palombi',
            'coordinates' => [3.497892, 44.523531],
        ],
        [
            'postalCode' => '23000',
            'name' => 'Patrick Bernard',
            'coordinates' => [1.871858, 46.1719],
        ],
        [
            'postalCode' => '15000',
            'name' => 'François Danemans',
            'coordinates' => [2.44461, 44.928588],
        ],
        [
            'postalCode' => '07000',
            'name' => 'Michel Coste',
            'coordinates' => [4.600135, 44.735372],
        ],
        [
            'postalCode' => '03000',
            'name' => 'Pierre Thépot',
            'coordinates' => [3.335909, 46.568577],
        ],
        [
            'postalCode' => '81000',
            'name' => 'Clement Baller',
            'coordinates' => [2.148, 43.9298],
        ],
        [
            'postalCode' => '75005,75006',
            'name' => 'Gilles Le Gendre',
            'coordinates' => [2.344212, 48.846246],
        ],
        [
            'postalCode' => '75012',
            'name' => 'Pierre-Alain Raphan',
            'coordinates' => [2.3876, 48.8412],
        ],
        [
            'postalCode' => '75016',
            'name' => 'Agnés Pannier',
            'coordinates' => [2.2769, 48.8637],
        ],
        [
            'postalCode' => '52000',
            'name' => 'Aude Crombé',
            'coordinates' => [5.1874, 48.1469],
        ],
        [
            'postalCode' => '28000',
            'name' => 'Guillaume Kasbarian',
            'coordinates' => [1.4892, 48.4469],
        ],
        [
            'postalCode' => '66000',
            'name' => 'Sebastien Cazenove',
            'coordinates' => [2.8954, 42.6976],
        ],
        [
            'postalCode' => '35000',
            'name' => 'Florian Bachelier',
            'coordinates' => [-1.6743, 48.112],
        ],
        [
            'postalCode' => '90000',
            'name' => 'Bruno Kern',
            'coordinates' => [6.8667, 47.6333],
        ],
        [
            'postalCode' => '54000',
            'name' => 'Carole Grandjean',
            'coordinates' => [6.185, 48.6844],
        ],
        [
            'postalCode' => '62000',
            'name' => 'Coralie Rembert',
            'coordinates' => [2.7819, 50.293],
        ],
        [
            'postalCode' => '12000',
            'name' => 'Thomas Mogharaei',
            'coordinates' => [2.5734, 44.3526],
        ],
        [
            'postalCode' => '14000',
            'name' => 'Elisabeth Mailloux Jaskulke',
            'coordinates' => [-0.3591, 49.1859],
        ],
        [
            'postalCode' => '79000',
            'name' => 'Johan Baufreton',
            'coordinates' => [-0.5129, 46.3021],
        ],
        [
            'postalCode' => '17000',
            'name' => 'Isabelle Vetois',
            'coordinates' => [-1.15, 46.1667],
        ],
        [
            'postalCode' => '75017',
            'name' => 'Laurent Saint-Martin',
            'coordinates' => [2.3219, 48.8835],
        ],
        [
            'postalCode' => '60000',
            'name' => 'Dominique Lelong',
            'coordinates' => [2.0833, 49.4333],
        ],
        [
            'postalCode' => '43000',
            'name' => 'Pierre Eteocle',
            'coordinates' => [3.8603, 45.0709],
        ],
        [
            'postalCode' => '83000',
            'name' => 'Valerie Lonchampt',
            'coordinates' => [5.9333, 43.1167],
        ],
        [
            'postalCode' => '75011',
            'name' => 'Marianna Mendza',
            'coordinates' => [2.3795, 48.8574],
        ],
        [
            'postalCode' => '47000',
            'name' => 'Olivier Damaisin',
            'coordinates' => [0.616995, 44.202338],
        ],
        [
            'postalCode' => '75009,75010',
            'name' => 'Celine Calvez',
            'coordinates' => [2.348584, 48.870625],
        ],
        [
            'postalCode' => '19000',
            'name' => 'Patricia Bordas',
            'coordinates' => [1.8008, 45.307],
        ],
        [
            'postalCode' => '75018',
            'name' => 'Justine Henry',
            'coordinates' => [2.3444, 48.8925],
        ],
        [
            'postalCode' => '24000',
            'name' => 'Michel Delpon',
            'coordinates' => [0.7167, 45.1833],
        ],
        [
            'postalCode' => '64000',
            'name' => 'Nathalie Niel',
            'coordinates' => [-0.3667, 43.3],
        ],
        [
            'postalCode' => '59000',
            'name' => 'Christophe Itier',
            'coordinates' => [3.0586, 50.633],
        ],
        [
            'postalCode' => '26000',
            'name' => 'Michel Gregoire',
            'coordinates' => [4.9, 44.9333],
        ],
        [
            'postalCode' => '60000',
            'name' => 'Richard Perrin',
            'coordinates' => [7.263545, 43.702555],
        ],
        [
            'postalCode' => '30000',
            'name' => 'Jerôme Talon',
            'coordinates' => [4.35, 43.8333],
        ],
        [
            'postalCode' => '27000',
            'name' => 'Fabien Gouttefarde',
            'coordinates' => [1.1508, 49.0241],
        ],
        [
            'postalCode' => '61000',
            'name' => 'Arnaud Bellet',
            'coordinates' => [0.1333, 48.45],
        ],
        [
            'postalCode' => '32000',
            'name' => 'Sylvie Theye',
            'coordinates' => [0.5833, 43.65],
        ],
        [
            'postalCode' => '88000',
            'name' => 'Serge Vincent',
            'coordinates' => [6.5153, 48.2001],
        ],
        [
            'postalCode' => '75007,75008',
            'name' => 'Veronique Tommasi',
            'coordinates' => [2.321, 48.8565],
        ],
        [
            'postalCode' => '40000',
            'name' => 'Caroline Breque',
            'coordinates' => [-0.4971, 43.8902],
        ],
        [
            'postalCode' => '05000',
            'name' => 'Bernard Renoy',
            'coordinates' => [-1.0725, 49.0894],
        ],
        [
            'postalCode' => '25000',
            'name' => 'Alexandra Cordier',
            'coordinates' => [6.0182, 47.2488],
        ],
        [
            'postalCode' => '45000',
            'name' => 'Emmanuel Constantin',
            'coordinates' => [1.9039, 47.9029],
        ],
        [
            'postalCode' => '86000',
            'name' => 'Philippe Chadeyron',
            'coordinates' => [0.3333, 46.5833],
        ],
        [
            'postalCode' => '94000',
            'name' => 'Laetitia Avia',
            'coordinates' => [2.4667, 48.7833],
        ],
        [
            'postalCode' => '69001',
            'name' => 'Bruno Bonnell',
            'coordinates' => [4.8345, 45.7676],
        ],
        [
            'postalCode' => '68000',
            'name' => 'Laurent Bernhard',
            'coordinates' => [7.3667, 48.0833],
        ],
        [
            'postalCode' => '02000',
            'name' => 'Cyril Thirion',
            'coordinates' => [3.621456, 49.563379],
        ],
        [
            'postalCode' => '16000',
            'name' => 'Thomas Mesnier',
            'coordinates' => [0.15, 45.65],
        ],
        [
            'postalCode' => '78000',
            'name' => 'Aziz-François Ndiaye',
            'coordinates' => [2.1333, 48.8],
        ],
        [
            'postalCode' => '11000',
            'name' => 'Marie-Héléne Regnier',
            'coordinates' => [2.35, 43.2167],
        ],
        [
            'postalCode' => '21000',
            'name' => 'Danielle Juban',
            'coordinates' => [5.0167, 47.3167],
        ],
        [
            'postalCode' => '75020',
            'name' => 'Anne-Lise Chagneau',
            'coordinates' => [2.3984, 48.8646],
        ],
        [
            'postalCode' => '20000',
            'name' => 'André de Cafarelli',
            'coordinates' => [8.737346, 41.926178],
        ],
        [
            'postalCode' => '58000',
            'name' => 'Amandine Boujlilat',
            'coordinates' => [3.1833, 46.95],
        ],
        [
            'postalCode' => '75019',
            'name' => 'Vitaly Goloubev',
            'coordinates' => [2.3822, 48.8817],
        ],
        [
            'postalCode' => '37000',
            'name' => 'Philippe Chalumeau',
            'coordinates' => [0.6833, 47.3833],
        ],
        [
            'postalCode' => '41000',
            'name' => 'Christine Jagueneau',
            'coordinates' => [1.2667, 47.6],
        ],
        [
            'postalCode' => '75013',
            'name' => 'Philippe Zaouati',
            'coordinates' => [2.3561, 48.8322],
        ],
        [
            'postalCode' => '34000',
            'name' => 'Coralie Dubost',
            'coordinates' => [3.8772, 43.6109],
        ],
        [
            'postalCode' => '87000',
            'name' => 'Stephane Bobin',
            'coordinates' => [1.2578, 45.8315],
        ],
        [
            'postalCode' => '91000',
            'name' => 'Laetitia Romeiro',
            'coordinates' => [2.45, 48.6333],
        ],
        [
            'postalCode' => '74000',
            'name' => 'Guillaume Gibouin',
            'coordinates' => [6.1167, 45.9],
        ],
        [
            'postalCode' => '38000',
            'name' => 'Didier Rambaud',
            'coordinates' => [5.7167, 45.1667],
        ],
        [
            'postalCode' => '77000',
            'name' => 'Stephanie Do',
            'coordinates' => [2.6636, 48.5088],
        ],
        [
            'postalCode' => '75001,75002,73003,75004',
            'name' => 'Isabelle de Courtivron',
            'coordinates' => [2.352391, 48.860521],
        ],
        [
            'postalCode' => '29000',
            'name' => 'Pierre Karleskind',
            'coordinates' => [-4.099999, 48],
        ],
        [
            'postalCode' => '22000',
            'name' => 'Eric Bothorel',
            'coordinates' => [-2.7684, 48.5151],
        ],
        [
            'postalCode' => '80000',
            'name' => 'Laurence David-Moalic',
            'coordinates' => [2.3, 49.9],
        ],
        [
            'postalCode' => '39000',
            'name' => 'Philippe Antoine',
            'coordinates' => [5.55, 46.6667],
        ],
        [
            'postalCode' => '974',
            'name' => 'Carine Garcia',
            'coordinates' => [55.386398, -21.130311],
        ],
        [
            'postalCode' => '33000',
            'name' => 'Tanguy Bernard',
            'coordinates' => [-0.5805, 44.8404],
        ],
        [
            'postalCode' => '44000',
            'name' => 'Valerie Oppelt',
            'coordinates' => [-1.5534, 47.2173],
        ],
        [
            'postalCode' => '72000',
            'name' => 'Marlène Schiappa',
            'coordinates' => [0.2, 48],
        ],
        [
            'postalCode' => '13001',
            'name' => 'Corinne Versini',
            'coordinates' => [5.3841, 43.2981],
        ],
        [
            'postalCode' => '92000',
            'name' => 'Laurianne Rossi',
            'coordinates' => [2.2067, 48.892],
        ],
        [
            'postalCode' => '73000',
            'name' => 'Jean-Christophe Masseron',
            'coordinates' => [5.9435, 45.585],
        ],
        [
            'postalCode' => '63000',
            'name' => 'Guy Lavocat',
            'coordinates' => [3.0863, 45.7797],
        ],
        [
            'postalCode' => '93000',
            'name' => 'Alexandre Aidara',
            'coordinates' => [2.45, 48.9],
        ],
        [
            'postalCode' => '85000',
            'name' => 'Stephane Buchou',
            'coordinates' => [-1.4546, 46.719],
        ],
        [
            'postalCode' => '51000',
            'name' => 'Aïna Kunic',
            'coordinates' => [4.3672, 48.9539],
        ],
        [
            'postalCode' => '75014',
            'name' => 'Alexandre Kimmerlé',
            'coordinates' => [2.3302196, 48.8335771],
        ],
        [
            'postalCode' => '57000',
            'name' => 'Beatrice Agamennone',
            'coordinates' => [6.1727, 49.1191],
        ],
        [
            'postalCode' => '67000',
            'name' => 'Bruno Studer',
            'coordinates' => [7.743, 48.5834],
        ],
        [
            'postalCode' => '75015',
            'name' => 'Fanta Berete',
            'coordinates' => [2.3003, 48.8412],
        ],
        [
            'postalCode' => '01000',
            'name' => 'Olga Givernet',
            'coordinates' => [5.226123, 46.202233],
        ],
        [
            'postalCode' => '70000',
            'name' => 'Loic Biver',
            'coordinates' => [6.045, 47.6018],
        ],
        [
            'postalCode' => '04000',
            'name' => 'Héléne Verny',
            'coordinates' => [6.233991, 44.088886],
        ],
        [
            'postalCode' => '05000',
            'name' => 'Bernard Renoy',
            'coordinates' => [6.08677, 44.564546],
        ],
        [
            'postalCode' => '36000',
            'name' => 'Pascal Maitre',
            'coordinates' => [1.692031, 46.802439],
        ],
        [
            'postalCode' => '56000',
            'name' => 'Jean-Michel Jacques',
            'coordinates' => [-2.758, 47.6575],
        ],
        [
            'postalCode' => '49000',
            'name' => 'Michel Bodet',
            'coordinates' => [-0.590805, 47.463141],
        ],
        [
            'postalCode' => '08000',
            'name' => 'Jean-Pierre Morali',
            'coordinates' => [4.700075, 49.768905],
        ],
        [
            'postalCode' => '76000',
            'name' => 'Damien Adam',
            'coordinates' => [1.0215152, 49.4412454],
        ],
        [
            'postalCode' => '31000-31500',
            'name' => 'Mickael Nogal',
            'coordinates' => [1.444209, 43.604652],
        ],
        [
            'postalCode' => '987',
            'name' => 'Heilama Gabert',
            'coordinates' => [-149.406843, -17.679742],
        ],
        [
            'postalCode' => '976',
            'name' => 'Sarah Mouhoussoune',
            'coordinates' => [45.166244, -12.8275],
        ],
        [
            'postalCode' => '971',
            'name' => 'Bernard Pancrel',
            'coordinates' => [-61.6298815, 16.2193115],
        ],
        [
            'postalCode' => '973',
            'name' => 'Isabelle Patient',
            'coordinates' => [-52.635498046875, 5.101887070062333],
        ],
    ];
}
