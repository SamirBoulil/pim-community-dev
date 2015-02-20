<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Published product repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 */
interface PublishedProductRepositoryInterface extends ProductRepositoryInterface
{
    /**
     * Fetch a published product by the working copy product id
     *
     * @param ProductInterface $originalProduct
     *
     * @return PublishedProductInterface
     */
    public function findOneByOriginalProduct(ProductInterface $originalProduct);

    /**
     * Fetch many published products by a list of working copy product ids
     *
     * @param ProductInterface[] $originalProducts
     *
     * @return PublishedProductInterface[]
     */
    public function findByOriginalProducts(array $originalProducts);

    /**
     * Get the version that has been published for a given original product ID.
     * If none version has been published, null is returned.
     *
     * @param integer|string $originalId
     *
     * @return int|null
     */
    public function getPublishedVersionIdByOriginalProductId($originalId);

    /**
     * Get the ID's of all published products.
     * The keys of the array are the ID of the original product.
     *
     * @param integer[] $originalIds
     *
     * @return array [ original product ID => published product ID ]
     */
    public function getProductIdsMapping(array $originalIds = []);

    /**
     * Count published products for a specific family
     *
     * @param Family $family
     *
     * @return integer
     */
    public function countPublishedProductsForFamily(Family $family);

    /**
     * Count published products for a specific category
     *
     * @param integer[] $categoryIds
     *
     * @return integer
     */
    public function countPublishedProductsForCategoryAndChildren($categoryIds);

    /**
     * Count published products for a specific attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return integer
     */
    public function countPublishedProductsForAttribute(AbstractAttribute $attribute);

    /**
     * Count published products for a specific group
     *
     * @param Group $group
     *
     * @return integer
     */
    public function countPublishedProductsForGroup(Group $group);

    /**
     * Count published products for a specific association type
     *
     * @param AssociationType $associationType
     *
     * @return integer
     */
    public function countPublishedProductsForAssociationType(AssociationType $associationType);

    /**
     * Count published products for a specific attribute option
     *
     * @param AttributeOption $option
     *
     * @return integer
     */
    public function countPublishedProductsForAttributeOption(AttributeOption $option);
}