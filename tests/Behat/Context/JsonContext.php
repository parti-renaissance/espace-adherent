<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behatch\Context\JsonContext as BehatchJsonContext;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Tests\App\Test\Helper\PHPUnitHelper;

class JsonContext extends BehatchJsonContext
{
    use PHPMatcherAssertions;

    public function theJsonShouldBeEqualTo(PyStringNode $content)
    {
        $this->assertJson($content->getRaw(), $this->getJson());
    }

    public function assertJson(string $expected, string $actual): void
    {
        $expected = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $expected);

        $this->match($expected, $actual);
    }

    /**
     * Checks, that given JSON nodes match values
     *
     * @Then the JSON nodes should match:
     */
    public function theJsonNodesShouldMatch(TableNode $nodes)
    {
        foreach ($nodes->getRowsHash() as $node => $text) {
            $this->theJsonNodeShouldMatch($node, $text);
        }
    }

    public function theJsonNodeShouldMatch($node, $text)
    {
        $actual = $this->inspector->evaluate($this->getJson(), $node);

        $this->match($text, \is_bool($actual) ? json_encode($actual) : (string) $actual);
    }

    protected function match($expected, $actual)
    {
        $this->assertMatchesPattern($expected, $actual);
    }

    /**
     * @Then /^the JSON should be a superset of:$/
     */
    public function theJsonIsASupersetOf(PyStringNode $content)
    {
        $actual = json_decode($this->httpCallResultPool->getResult()->getValue(), true);
        PHPUnitHelper::assertArraySubset(json_decode($content->getRaw(), true), $actual);
    }
}
