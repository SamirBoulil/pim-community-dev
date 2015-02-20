<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes as BaseEditCommonAttributes;
use Symfony\Component\Security\Core\SecurityContextInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 */
class EditCommonAttributes extends BaseEditCommonAttributes
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param ProductManager           $productManager
     * @param UserContext              $userContext
     * @param CurrencyManager          $currencyManager
     * @param CatalogContext           $catalogContext
     * @param ProductBuilder           $productBuilder
     * @param ProductMassActionManager $massActionManager
     * @param MetricFactory            $metricFactory
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ProductManager $productManager,
        UserContext $userContext,
        CurrencyManager $currencyManager,
        CatalogContext $catalogContext,
        ProductBuilder $productBuilder,
        ProductMassActionManager $massActionManager,
        MetricFactory $metricFactory,
        array $classes,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct(
            $productManager,
            $userContext,
            $currencyManager,
            $catalogContext,
            $productBuilder,
            $massActionManager,
            $metricFactory,
            $classes
        );

        $this->securityContext = $securityContext;
    }

    /**
     * Get form options
     *
     * @return array
     */
    public function getFormOptions()
    {
        return array(
            'locales'          => $this->userContext->getGrantedUserLocales(Attributes::EDIT_PRODUCTS),
            'common_attributes' => $this->commonAttributes,
            'current_locale' => $this->getLocale()->getCode()
        );
    }

    /**
     * {@inheritdoc}
     *
     * Prevent performing operation if current user does not own the product
     * Otherwise, product is directly updated and propostion is also created
     */
    protected function doPerform(ProductInterface $product)
    {
        if ($this->securityContext->isGranted(Attributes::OWN, $product)) {
            return parent::doPerform($product);
        }
    }
}