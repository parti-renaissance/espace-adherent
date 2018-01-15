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

        // Replace all useless whitespace
        $expected = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $content->getRaw());

        if (!PHPMatcher::match((string) $actual, (string) $expected, $error)) {
            throw new ExpectationException($error, $this->getSession());
        }
    }
}
