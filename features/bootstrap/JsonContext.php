<?php

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ExpectationException;
use Behatch\Context\JsonContext as BehatchJsonContext;
use Coduo\PHPMatcher\PHPMatcher;

class JsonContext extends BehatchJsonContext
{
    public function theJsonShouldBeEqualTo(PyStringNode $content)
    {
        $actual = $this->getJson();

        $this->assertJson($content->getRaw(), $actual);
    }

    public function assertJson(string $expected, string $actual): void
    {
        // Remove all useless whitespaces
        $expected = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $expected);

        if (!PHPMatcher::match($actual, $expected, $error)) {
            throw new ExpectationException($error, $this->getSession());
        }
    }
}
