<?php

use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use Webmozart\Assert\Assert;

class FeatureContext extends RawMinkContext
{
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
            throw new \Exception(sprintf('Found %d occurences of "%s" when expecting %d', $count, $text, $times));
        }
    }

    /**
     * @Then I should be on :url wait otherwise
     */
    public function assertPageAddressAfterAllRedirection(string $url, int $ttl = 3, int $try = 1): void
    {
        $sleep = 100000; // 0.1 second

        try {
            $this->assertSession()->addressEquals($this->locatePath($url));
        } catch (ExpectationException $e) {
            if ($ttl <= $try * $sleep / 1000000) {
                throw $e;
            }

            usleep($sleep); // 0.1 second
            $this->assertPageAddressAfterAllRedirection($url, $ttl, ++$try);
        }
    }

    /**
     * @When I click the :cssElementSelector selector
     */
    public function clickElementSelector($cssElementSelector)
    {
        $field = $this->getSession()->getPage()->find('css', $cssElementSelector);

        Assert::notNull($field, "Cannot find '$cssElementSelector'");

        $field->click();
    }

    /**
     * @Then I accept terms of use
     */
    public function acceptTermsOfUse()
    {
        $modalElement = '#chart_dialog + .ui-dialog-buttonpane > .ui-dialog-buttonset > button';
        $termsOfUseModal = $this->getSession()->getPage()->find('css', $modalElement);
        if (null !== $termsOfUseModal) {
            $termsOfUseModal->click();
        }
    }

    /**
     * Scroll HTML element into view
     *
     * @Then I scroll element :cssSelector into view
     */
    public function iScrollElementIntoView($cssSelector): void
    {
        $js = <<<JS
document.querySelector("$cssSelector").scrollIntoView();
JS;

        $this->getSession()->executeScript($js);
    }
}
