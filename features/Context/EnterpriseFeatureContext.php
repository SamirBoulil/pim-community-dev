<?php

namespace Context;

use Behat\Behat\Context\Step;

/**
 * A context for creating entities
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFeatureContext extends FeatureContext
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->useContext('fixtures', new EnterpriseFixturesContext());
        $this->useContext('catalogConfiguration', new CatalogConfigurationContext());
        $this->useContext('webUser', new EnterpriseWebUser($parameters['window_width'], $parameters['window_height']));
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new DataGridContext());
        $this->useContext('command', new CommandContext());
        $this->useContext('navigation', new NavigationContext());
        $this->useContext('transformations', new TransformationContext());
        $this->useContext('assertions', new AssertionContext());
    }

    /**
     * @BeforeScenario
     */
    public function registerConfigurationDirectory()
    {
        $this
            ->getSubcontext('catalogConfiguration')
            ->addConfigurationDirectory(__DIR__.'/catalog');
    }

    /**
     * @Then /^I should see that (.*) is a modified value$/
     */
    public function iShouldSeeThatFieldIsAModifiedValue($field)
    {
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($field);
        foreach ($icons as $icon) {
            if ($icon->hasClass('icon-file-text-alt')) {
                return true;
            }
        }

        throw $this->createExpectationException('Modified value icon was not found');
    }

    /**
     * @Then /^its status should be "([^"]*)"$/
     */
    public function itsStatusShouldBe($status)
    {
        $info = $this->getSession()->getPage()->find('css', '.navbar-content li:contains("Status")');

        if (false === strpos($info->getText(), $status)) {
            throw new \LogicException(
                sprintf(
                    'Expecting product status "%s", actually is "%s"',
                    $status,
                    $info->getText()
                )
            );
        }
    }
}
