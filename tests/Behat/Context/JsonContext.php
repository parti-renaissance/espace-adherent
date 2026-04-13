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

    public function theJsonShouldBeEqualTo(PyStringNode $content): void
    {
        $this->assertJson($content->getRaw(), (string) $this->getJson());
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
    public function theJsonNodesShouldMatch(TableNode $nodes): void
    {
        foreach ($nodes->getRowsHash() as $node => $text) {
            $this->theJsonNodeShouldMatch($node, $text);
        }
    }

    public function theJsonNodeShouldMatch($node, $text): void
    {
        $actual = $this->inspector->evaluate($this->getJson(), $node);

        $this->match($text, \is_bool($actual) ? json_encode($actual) : (string) $actual);
    }

    protected function match($expected, $actual)
    {
        $this->assertMatchesPattern($expected, $actual);
    }

    /**
     * @Then the JSON node :node should contain an element with :key equal to :value
     */
    public function theJsonNodeShouldContainAnElementWith(string $node, string $key, string $value): void
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        foreach ($actual as $element) {
            if (isset($element->{$key}) && (string) $element->{$key} === $value) {
                return;
            }
        }

        throw new \Exception(\sprintf('The JSON node "%s" does not contain an element with "%s" equal to "%s"', $node, $key, $value));
    }

    /**
     * @Then the JSON node :node should not contain an element with :key equal to :value
     */
    public function theJsonNodeShouldNotContainAnElementWith(string $node, string $key, string $value): void
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        foreach ($actual as $element) {
            if (isset($element->{$key}) && (string) $element->{$key} === $value) {
                throw new \Exception(\sprintf('The JSON node "%s" contains an element with "%s" equal to "%s"', $node, $key, $value));
            }
        }
    }

    /**
     * @Then /^the JSON should be a superset of:$/
     */
    public function theJsonIsASupersetOf(PyStringNode $content): void
    {
        $actual = json_decode($this->httpCallResultPool->getResult()->getValue(), true);
        PHPUnitHelper::assertArraySubset(json_decode($content->getRaw(), true), $actual);
    }
}
