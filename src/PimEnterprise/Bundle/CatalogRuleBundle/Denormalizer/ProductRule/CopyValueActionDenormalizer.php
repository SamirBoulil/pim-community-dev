<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Denormalize product copy value rule actions
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class CopyValueActionDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $copyValueActionClass;

    /**
     * @param string $copyValueActionClass should implement
     *                                     \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface
     */
    public function __construct($copyValueActionClass)
    {
        $this->copyValueActionClass = $copyValueActionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $this->copyValueActionClass($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->copyValueActionClass &&
            isset($data['type']) &&
            ProductCopyValueActionInterface::TYPE === $data['type'];
    }
}
