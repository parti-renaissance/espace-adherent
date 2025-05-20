<?php

namespace App\DataFixtures\ORM;

use App\Entity\VotingPlatform\Designation\Poll\Poll;
use App\Entity\VotingPlatform\Designation\Poll\PollQuestion;
use App\Entity\VotingPlatform\Designation\Poll\QuestionChoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadDesignationPollData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($poll = new Poll());
        $poll->label = 'Petit questionnaire';
        $poll->addQuestion($question = new PollQuestion());
        $question->content = 'Est-ce qu\'il fait beau aujourd\'hui ?';

        $question->addChoice($choice = new QuestionChoice());
        $choice->label = 'Oui';

        $question->addChoice($choice = new QuestionChoice());
        $choice->label = 'Non, pas vraiment';
        $this->setReference('designation-poll-1', $poll);

        $manager->persist($poll = new Poll());
        $poll->label = 'Questionnaire pour la consultation (20 questions)';

        for ($i = 1; $i <= 20; ++$i) {
            $poll->addQuestion($question = new PollQuestion());
            $question->content = 'Question '.$i.' : Êtes-vous d\'accord avec la loi #'.random_int(1000, 9999).' ?';
            if (0 === $i % 2) {
                $question->description = <<<'EOT'
                    ## Aperçu

                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque at consequat risus. Cras rutrum, tortor ac commodo pretium, ipsum erat dignissim eros, sed laoreet urna libero sed ex.

                    ### Objectifs

                    - Integer porta turpis a orci tincidunt, ac iaculis purus iaculis.
                    - Curabitur vehicula ante vel eros elementum fermentum.
                    - Donec feugiat mi in augue pharetra, ac feugiat purus cursus.

                    ### Méthodologie

                    1. Sed eu turpis nec magna tristique fermentum.
                    2. Suspendisse sagittis massa in risus blandit, ac porta metus volutpat.
                    3. Vivamus at lorem sed justo efficitur congue.

                    ### Conclusion

                    Quisque nec tellus ut justo posuere sagittis. Fusce viverra justo vitae sem suscipit, non placerat elit blandit. Etiam at semper turpis, nec faucibus enim. Morbi sed justo vitae nisl hendrerit ullamcorper.
                    EOT;
            }

            $question->addChoice($choice = new QuestionChoice());
            $choice->label = 'Oui';

            $question->addChoice($choice = new QuestionChoice());
            $choice->label = 'Non';
        }

        $this->setReference('designation-poll-2', $poll);

        $manager->flush();
    }
}
