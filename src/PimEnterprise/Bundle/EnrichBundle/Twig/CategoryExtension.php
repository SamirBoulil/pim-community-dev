<?php

namespace PimEnterprise\Bundle\EnrichBundle\Twig;

use Pim\Bundle\EnrichBundle\Twig\CategoryExtension as PimCategoryExtension;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Overriden Twig extension to allow to count products or published products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryExtension extends PimCategoryExtension
{
    /**
     * @var ProductCategoryManager
     */
    protected $publishedManager;

    /**
     * Constructor
     *
     * @param ProductCategoryManager $manager
     * @param ProductCategoryManager $publishedManager
     */
    public function __construct(ProductCategoryManager $manager, ProductCategoryManager $publishedManager)
    {
        parent::__construct($manager);
        $this->publishedManager = $publishedManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function countProducts(CategoryInterface $category, $includeSub, $relatedEntity)
    {
        if ($relatedEntity === 'published_product') {
            return $this->publishedManager->getProductsCountInCategory($category, $includeSub);
        } else {
            return $this->manager->getProductsCountInCategory($category, $includeSub);
        }
    }
}
