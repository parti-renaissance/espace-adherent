<?php

namespace App\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class DoctrineMigrationCleanFixer extends AbstractFixer
{
    private $candidateTokens = [\T_DECLARE, \T_DOC_COMMENT, \T_COMMENT, \T_STRING];

    public function getName(): string
    {
        return 'App/'.parent::getName();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Remove declare(strict_types=1), auto-generated comments, and abortIf calls from doctrine migration generated files.',
            [
                new CodeSample(
                    '<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190306110954 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== \'mysql\', \'Migration can only be executed safely on \'mysql\'.\');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== \'mysql\', \'Migration can only be executed safely on \'mysql\'.\');
    }
}'
                ),
            ]
        );
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function supports(\SplFileInfo $file): bool
    {
        return preg_match("/^Version\d{14}/", $file->getBasename());
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound($this->candidateTokens);
    }

    public function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(\T_DECLARE)) {
                $tokens->clearRange(
                    $index,
                    $tokens->getTokenOfKindSibling($index, 1, [[\T_WHITESPACE]])
                );
            }

            if ($token->isGivenKind(\T_DOC_COMMENT)
                && str_contains($token->getContent(), 'Auto-generated Migration: Please modify to your needs!')
            ) {
                $tokens->clearRange(
                    $index,
                    $tokens->getTokenOfKindSibling($index, 1, [[\T_WHITESPACE]])
                );
            }

            if ($token->isGivenKind(\T_COMMENT)
                && str_contains($token->getContent(), 'auto-generated')
            ) {
                $tokens->clearRange(
                    $index,
                    $tokens->getTokenOfKindSibling($index, 1, [[\T_WHITESPACE]])
                );
            }

            if ($token->isGivenKind(\T_STRING) && 'abortIf' === $token->getContent()) {
                $tokens->clearRange(
                    $tokens->getTokenOfKindSibling($index, -1, [[\T_WHITESPACE]]),
                    $endOfTheLineTokenIndex = $tokens->getNextTokenOfKind($index, [';'])
                );
            }
        }
    }
}
