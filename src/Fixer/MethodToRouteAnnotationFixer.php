<?php

namespace App\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class MethodToRouteAnnotationFixer extends AbstractFixer
{
    public function getName(): string
    {
        return 'App/'.parent::getName();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Replace @Method annotation by @Route(..., methods={})',
            [
                new CodeSample(
                    '<?php 
/**
 * @Route("/hello-world", name="hello_world")
 * @Method("GET")
 */
public function helloWorldAction(){
//...
}                          
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
        return $tokens->isAllTokenKindsFound([\T_DOC_COMMENT]);
    }

    public function supports(\SplFileInfo $file): bool
    {
        return preg_match('/Controller$/', $file->getBasename('.php'));
    }

    public function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $token) {
            if (!$token->isGivenKind([\T_DOC_COMMENT])) {
                continue;
            }

            if ($token->isGivenKind(\T_DOC_COMMENT)
                && false !== strpos($token->getContent(), '@Route')
                && false !== strpos($token->getContent(), '@Method')
            ) {
                $token->setContent($this->replaceMethodToRouteAnnotation($token->getContent()));
            }
        }
    }

    private function replaceMethodToRouteAnnotation(string $content): string
    {
        $methodAnnotationStart = strpos($content, '@Method');
        $methodAnnotationEnd = strpos($content, ')', $methodAnnotationStart) + 1;

        return $this->addMethodArgInRoute(
            $this->removeMethodLine($content, $methodAnnotationEnd),
            $this->getMethodsToMove(substr(
                $content,
                $methodAnnotationStart,
                $methodAnnotationEnd - $methodAnnotationStart
            ))
        );
    }

    private function addMethodArgInRoute(string $content, array $methodsToMove): string
    {
        $routeAnnotationStart = strpos($content, '@Route');
        $openingBraceOfRoute = strpos($content, '(', $routeAnnotationStart);

        if (false !== $openingBraceOfFunction = strpos($content, 'condition', $openingBraceOfRoute + 1)) {
            $closingBraceOfFunction = strpos($content, '"', $openingBraceOfFunction + \strlen('condition=') + 1);
            $routeAnnotationEnd = strpos($content, ')', $closingBraceOfFunction + 1);
        } else {
            $routeAnnotationEnd = strpos($content, ')', $routeAnnotationStart);
        }

        $routeLine = substr($content, $routeAnnotationStart, $routeAnnotationEnd - $routeAnnotationStart);

        $newRouteLine = substr_count($routeLine, "\n") > 1
            ? $this->addMethodArgInMultiLineRoute($routeLine, $methodsToMove)
            : $this->addMethodArgInSingleLineRoute($routeLine, $methodsToMove)
        ;

        return str_replace($routeLine, $newRouteLine, $content);
    }

    private function addMethodArgInMultiLineRoute(string $routeLine, array $methodsToMove): string
    {
        $lastLineBreak = strrpos($routeLine, "\n");
        $replacement = ",\n     *     methods={".implode(', ', $methodsToMove).'}';

        return substr_replace($routeLine, $replacement, $lastLineBreak, 0);
    }

    private function addMethodArgInSingleLineRoute(string $routeLine, array $methodsToMove): string
    {
        return str_replace($routeLine, $routeLine.', methods={'.implode(', ', $methodsToMove).'}', $routeLine);
    }

    private function removeMethodLine(string $content, int $methodAnnotationEnd): string
    {
        $newLineIndexes = $this->getNewLineIndexes($content);
        $startIndexToRemove = $newLineIndexes[array_search($methodAnnotationEnd, $newLineIndexes) - 1] + 1;
        $endIndexToRemove = $methodAnnotationEnd + \strlen("\n");
        $lineToRemove = substr($content, $startIndexToRemove, $endIndexToRemove - $startIndexToRemove);

        return str_replace($lineToRemove, '', $content);
    }

    private function getNewLineIndexes(string $content): array
    {
        $lastPos = 0;
        $indexes = [];

        while (false !== ($lastPos = strpos($content, "\n", $lastPos))) {
            $indexes[] = $lastPos;
            $lastPos = $lastPos + \strlen("\n");
        }

        return $indexes;
    }

    private function getMethodsToMove(string $methodLine): array
    {
        $methodLine = str_replace('|', ',', $methodLine);
        $methodLine = str_replace('"', '', $methodLine);
        $methodLine = str_replace('\'', '', $methodLine);
        $methodLine = str_replace('{', '', $methodLine);
        $methodLine = str_replace('}', '', $methodLine);
        $parenthesisStart = strpos($methodLine, '(') + 1;
        $parenthesisEnd = strpos($methodLine, ')');
        $methodArgs = substr($methodLine, $parenthesisStart, $parenthesisEnd - $parenthesisStart);
        $methodArgs = explode(',', $methodArgs);

        foreach ($methodArgs as $key => $arg) {
            $methodArgs[$key] = '"'.trim($arg).'"';
        }

        return $methodArgs;
    }
}
