<?php

use Behat\MinkExtension\Context\RawMinkContext;
use Carbon\Carbon;
use Webmozart\Assert\Assert;

class FeatureContext extends RawMinkContext
{
    /**
     * @Given (I )freeze the clock to :dateTime
     */
    public function freezeClock(string $dateTime): void
    {
        Carbon::setTestNow($dateTime);
    }

    /**
     * @AfterScenario
     */
    public function defreezeClock(): void
    {
        Carbon::setTestNow();
    }

    /**
     * @Given I resolved the captcha
     */
    public function iResolvedTheCaptcha()
    {
        $this->getSession()->getPage()->find('css', 'input[name="g-recaptcha-response"]')->setValue('dummy');
    }

    /**
     * @When I fill in hidden field :fieldId with :value
     */
    public function fillField($fieldId, $value)
    {
        $this->getSession()->getPage()->findById($fieldId)->setValue($value);
    }

    /**
     * @When I click the :elementId element
     */
    public function clickLinkElement($elementId)
    {
        $field = $this->getSession()->getPage()->findById($elementId);

        Assert::notNull($field, 'Cannot find "'.$elementId.'"');

        $field->click();
    }

    /**
     * @Then I should see :text :times times
     */
    public function iShouldSeeTextManyTimes(string $text, int $times)
    {
        $count = substr_count($this->getSession()->getPage()->getText(), $text);

        if ($times !== $count) {
            throw new \Exception(
                sprintf('Found %d occurences of "%s" when expecting %d', $count, $text, $times)
            );
        }
    }
}
