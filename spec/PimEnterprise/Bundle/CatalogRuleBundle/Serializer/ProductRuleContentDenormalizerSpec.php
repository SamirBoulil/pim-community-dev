<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductCopyValueActionNormalizer;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleConditionNormalizer;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductSetValueActionNormalizer;
use Prophecy\Argument;

class ProductRuleContentDenormalizerSpec extends ObjectBehavior
{
    public function let(
        ProductRuleConditionNormalizer $conditionNormalizer,
        ProductSetValueActionNormalizer $setValueActionNormalizer,
        ProductCopyValueActionNormalizer $copyValueActionNormalizer
    ) {
        $this->beConstructedWith(
            $conditionNormalizer,
            $setValueActionNormalizer,
            $copyValueActionNormalizer,
            'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_a_product_rule_content()
    {
        $content = $this->buildRuleContent();

        // TODO: use a custom matcher to test it
        $this->denormalize($content, 'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule');
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_no_conditions_key()
    {
        $content = [
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" can not be denormalized.', json_encode($content)))
        )->during('denormalize', [$content, 'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule']);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_no_actions_key()
    {
        $content = [
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" can not be denormalized.', json_encode($content)))
        )->during('denormalize', [$content, 'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule']);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_no_action_type()
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" has an action with no type.', json_encode($content)))
        )->during('denormalize', [$content, 'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule']);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_an_invalid_action_type()
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'unknown_action', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" has an unknown type of action "unknown_action".', json_encode($content)))
        )->during('denormalize', [$content, 'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule']);
    }

    /**
     * Do not delete it, this method is used to easily build the rule content that is
     * used in those specs.
     * In case we need to modify the specs, it will be useful.
     *
     * @param bool $encode
     *
     * @return string
     */
    private function buildRuleContent()
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        return $content;
    }
}
