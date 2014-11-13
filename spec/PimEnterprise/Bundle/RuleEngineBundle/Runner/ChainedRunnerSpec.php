<?php

namespace spec\PimEnterprise\Bundle\RuleEngineBundle\Runner;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Prophecy\Argument;

class ChainedRunnerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Runner\ChainedRunner');
    }

    function it_should_be_a_runner_and_a_dry_runner()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface');
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Runner\DryRunnerInterface');
    }

    function it_supports_all_rules(RuleInterface $rule)
    {
        $this->supports($rule)->shouldReturn(true);
    }

    function it_runs_a_rule(RuleInterface $rule, RunnerInterface $runner1, RunnerInterface $runner2)
    {
        $runner1->supports(Argument::any())->willReturn(false);
        $runner2->supports(Argument::any())->willReturn(true);
        $runner1->run(Argument::any())->willReturn('Runner1 launched');
        $runner2->run(Argument::any())->willReturn('Runner2 launched');

        $this->addRunner($runner1);
        $this->addRunner($runner2);
        $this->run($rule)->shouldReturn('Runner2 launched');
    }

    function it_throws_an_exception_when_no_runner_supports_the_rule(RuleInterface $rule)
    {
        $rule->getCode()->willReturn('therule');
        $this->shouldThrow(new \LogicException('No runner available for the rule "therule".'))->during('run', [$rule]);
    }

    function it__dry_runs_a_rule(
        RuleInterface $rule,
        DryRunnerInterface $runner1,
        RunnerInterface $runner2,
        DryRunnerInterface $runner3
    ) {
        $runner1->supports(Argument::any())->willReturn(false);
        $runner2->supports(Argument::any())->willReturn(true);
        $runner3->supports(Argument::any())->willReturn(true);
        $runner1->dryRun(Argument::any())->willReturn('Runner1 launched');
        $runner2->run(Argument::any())->willReturn('Runner2 launched');
        $runner3->dryRun(Argument::any())->willReturn('Runner3 launched');

        $this->addRunner($runner1);
        $this->addRunner($runner2);
        $this->addRunner($runner3);
        $this->dryRun($rule)->shouldReturn('Runner3 launched');
    }

    function it_throws_an_exception_when_no_dry_runner_supports_the_rule(RuleInterface $rule)
    {
        $rule->getCode()->willReturn('therule');
        $this->shouldThrow(new \LogicException('No dry runner available for the rule "therule".'))
            ->during('dryRun', [$rule]);
    }
}
