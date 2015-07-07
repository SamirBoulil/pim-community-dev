<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;

class RuleDefinitionWriterSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $saver
    ) {
        $this->beConstructedWith($saver);
    }

    function it_implements()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_writes_a_rule_definition(
        $saver,
        RuleDefinition $rule1,
        RuleDefinition $rule2,
        StepExecution $stepExecution
    ) {
        $rule1->getId()->willReturn(42);
        $items = [$rule1, $rule2];

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('update')->shouldBeCalledTimes(1);

        $this->setStepExecution($stepExecution);

        $saver->saveAll($items)->shouldBeCalled();

        $this->write($items);
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }
}