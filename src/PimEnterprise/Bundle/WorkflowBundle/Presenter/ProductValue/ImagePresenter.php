<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Present an image value
 *
 * @author    Filips Alpe <filips@akeneo.com>
 */
class ImagePresenter extends FilePresenter
{
    /** @staticvar string */
    const TEMPLATE = 'PimEnterpriseWorkflowBundle:ProductValue:image.html.twig';

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $value)
    {
        return 'pim_catalog_image' === $value->getAttribute()->getAttributeType()
            && $value->getData() instanceof AbstractProductMedia
            && 0 === strpos($value->getData()->getMimeType(), 'image');
    }
}
