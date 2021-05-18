<?php

namespace App\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class SensioToSymfonyRouteFixer extends AbstractFixer
{
    public function getName(): string
    {
        return 'App/'.parent::getName();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Replace Sensio\Bundle\FrameworkExtraBundle\Configuration\Route by Symfony\Component\Routing\Annotation\Route',
            [
                new CodeSample(
                    '<?php 

namespace App\Controller;

- use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
+ use Symfony\Component\Routing\Annotation\Route;                       
'
                ),
            ]
        );
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_USE, \T_DOC_COMMENT]);
    }

    public function supports(\SplFileInfo $file): bool
    {
        return preg_match('/Controller$/', $file->getBasename('.php'));
    }

    public function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $isAimedRoute = false;
        $hasServiceOption = false;
        $useTokenIndexStart = 0;
        $useTokenIndexEnd = 0;

        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind([\T_USE, \T_DOC_COMMENT])) {
                continue;
            }

            if ($token->isGivenKind(\T_USE) && !$isAimedRoute) {
                $useTokenIndexStart = $index;
                $useTokenIndexEnd = $tokens->getNextTokenOfKind($index, [';']) + 1;

                $useTokens = \array_slice($tokens->toArray(), $useTokenIndexStart, $useTokenIndexEnd - $useTokenIndexStart);

                $isAimedRoute = $this->isSensioBundleFrameworkExtraBundleConfigurationRoute($useTokens);
            }

            if ($token->isGivenKind(\T_DOC_COMMENT) && !$hasServiceOption) {
                $hasServiceOption = $this->hasServiceOption($token->getContent());
            }

            if ($isAimedRoute
                && !$hasServiceOption
                && $useTokenIndexStart > 0
                && $useTokenIndexEnd > 0
                && $index === $tokens->getTokenOfKindSibling(\count($tokens), -1, [[\T_DOC_COMMENT]])
            ) {
                $tokens->clearRange(
                    $useTokenIndexStart,
                    $useTokenIndexEnd
                );

                $tokens[$useTokenIndexStart]->setContent("use Symfony\Component\Routing\Annotation\Route;\n");
            }
        }
    }

    private function isSensioBundleFrameworkExtraBundleConfigurationRoute(array $tokens): bool
    {
        $namespace = implode('',
            array_map(
                function ($token) {
                    return $token->getContent();
                },
                $tokens
            )
        );

        return 'use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;' === $namespace;
    }

    private function hasServiceOption(string $content): bool
    {
        return false !== strpos($content, '@Route') && false !== strpos($content, 'service');
    }
}
