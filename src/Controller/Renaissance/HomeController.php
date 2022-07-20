<?php

namespace App\Controller\Renaissance;

use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="app_ren_homepage", methods={"GET"})
 */
class HomeController extends AbstractController
{
    private const VALUES = [
        [
            'title' => 'La République 1',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 2',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 3',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 4',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 5',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 6',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 7',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 8',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 9',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 10',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 11',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 12',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 13',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 14',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 15',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 16',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 17',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 18',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 19',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 20',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
        [
            'title' => 'La République 21',
            'short_description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.',
            'description' => 'Lorem ipsum dolor sit amet. Et nulla iusto At galisum molestiae aut exercitationem totam sit eaque delectus. Est magni labore ab nesciunt quae in quis nisi et cumque optio. Et molestiae repellat qui omnis illo sit dolor doloribus.
            Et beatae quibusdam et ipsam aliquam sed dolorum galisum sed adipisci voluptas ut porro commodi. Aut architecto quia a recusandae deleniti qui nulla sint cum modi galisum sed deleniti possimus.
            Eum odio velit eum voluptatem magni eos nisi molestiae. Et quia consequatur qui officia eaque nam ullam facilis. Nam voluptas quisquam et natus sint et dolores quia sed cupiditate quisquam. Et error dolor quo itaque sunt est dolor galisum hic facilis tenetur.',
        ],
    ];

    public function __invoke(): Response
    {
        $slugifier = Slugify::create();

        return $this->render('renaissance/home.html.twig', [
            'values' => array_map(function (array $row) use ($slugifier) {
                $row['slug'] = $slugifier->slugify($row['title']);

                return $row;
            }, self::VALUES),
        ]);
    }
}
