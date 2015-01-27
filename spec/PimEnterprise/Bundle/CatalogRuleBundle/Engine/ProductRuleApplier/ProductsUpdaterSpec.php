<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

class ProductsUpdaterSpec extends ObjectBehavior
{
    function let(
        ProductUpdaterInterface $productUpdater,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->beConstructedWith(
            $productUpdater,
            $templateUpdater
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater');
    }

    function it_does_not_update_products_when_no_actions(
        $productUpdater,
        RuleInterface $rule,
        ProductInterface $product
    ) {
        $rule->getActions()->willReturn([]);

        $productUpdater->setValue(Argument::any())->shouldNotBeCalled();
        $productUpdater->copyValue(Argument::any())->shouldNotBeCalled();

        $this->update([$product], $rule);
    }

    function it_updates_product_when_the_rule_has_a_set_action(
        $productUpdater,
        RuleInterface $rule,
        ProductInterface $product,
        ProductSetValueActionInterface $action
    ) {
        $action->getField()->willReturn('sku');
        $action->getValue()->willReturn('foo');
        $action->getScope()->willReturn('ecommerce');
        $action->getLocale()->willReturn('en_US');
        $rule->getActions()->willReturn([$action]);

        $productUpdater->setValue(Argument::any(), 'sku', 'foo', 'en_US', 'ecommerce')->shouldBeCalled();

        $this->update([$product], $rule);
    }

    function it_updates_product_when_the_rule_has_a_copy_action(
        $productUpdater,
        RuleInterface $rule,
        ProductInterface $product,
        ProductCopyValueAction $action
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('description');
        $action->getFromLocale()->willReturn('fr_FR');
        $action->getToLocale()->willReturn('fr_CH');
        $action->getFromScope()->willReturn('ecommerce');
        $action->getToScope()->willReturn('tablet');
        $rule->getActions()->willReturn([$action]);

        $productUpdater
            ->copyValue([$product], 'sku', 'description', 'fr_FR', 'fr_CH', 'ecommerce', 'tablet')
            ->shouldBeCalled();

        $this->update([$product], $rule);
    }

    function it_throws_exception_when_update_a_product_with_an_unknown_action(
        RuleInterface $rule,
        ProductInterface $product
    ) {
        $rule->getActions()->willReturn([new \stdClass()]);
        $rule->getCode()->willReturn('test_rule');

        $this->shouldThrow(new \LogicException('The action "stdClass" is not supported yet.'))
            ->during('update', [[$product], $rule]);
    }
}
