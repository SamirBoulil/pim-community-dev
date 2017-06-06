<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\Localization\Model\LocalizableInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CommentBundle\Model\CommentSubjectInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Product interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductInterface extends
    LocalizableInterface,
    ScopableInterface,
    TimestampableInterface,
    VersionableInterface,
    CommentSubjectInterface,
    ReferableInterface,
    CategoryAwareInterface
{
    /**
     * Get the ID of the product
     *
     * @return UuidInterface
     */
    public function getId();

    /**
     * Set id
     *
     * @param UuidInterface $id
     *
     * @return ProductInterface
     */
    public function setId(UuidInterface $id);

    /**
     * Get the identifier of the product
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * @param ProductValueInterface $identifier
     *
     * @return ProductInterface
     *
     */
    public function setIdentifier(ProductValueInterface $identifier);

    /**
     * @return array
     */
    public function getRawValues();

    /**
     * @param array $rawValues
     *
     * @return ProductInterface
     */
    public function setRawValues(array $rawValues);

    /**
     * Get values
     *
     * @return ProductValueCollectionInterface
     */
    public function getValues();

    /**
     * Set values
     *
     * @param ProductValueCollectionInterface $values
     *
     * @return ProductInterface
     */
    public function setValues(ProductValueCollectionInterface $values);

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     *
     * @return ProductValueInterface
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null);

    /**
     * Add value, override to deal with relation owner side
     *
     * @param ProductValueInterface $value
     *
     * @return ProductInterface
     */
    public function addValue(ProductValueInterface $value);

    /**
     * Remove value
     *
     * @param ProductValueInterface $value
     *
     * @return ProductInterface
     */
    public function removeValue(ProductValueInterface $value);

    /**
     * Get the product groups
     *
     * @return Collection
     */
    public function getGroups();

    /**
     * Add a group
     *
     * @param GroupInterface $group
     *
     * @return ProductInterface
     */
    public function addGroup(GroupInterface $group);

    /**
     * Remove a group
     *
     * @param GroupInterface $group
     *
     * @return ProductInterface
     */
    public function removeGroup(GroupInterface $group);

    /**
     * Get ordered group
     *
     * @return array
     */
    public function getOrderedGroups();

    /**
     * Get the variant group of the product
     *
     * @return GroupInterface|null
     */
    public function getVariantGroup();

    /**
     * Get groups code
     *
     * @return array
     */
    public function getGroupCodes();

    /**
     * Get types of associations
     *
     * @return Collection
     */
    public function getAssociations();

    /**
     * Set types of associations
     *
     * @param AssociationInterface[] $associations
     *
     * @return ProductInterface
     */
    public function setAssociations(array $associations = []);

    /**
     * Add a type of an association
     *
     * @param AssociationInterface $association
     *
     * @throws \LogicException
     *
     * @return ProductInterface
     */
    public function addAssociation(AssociationInterface $association);

    /**
     * Remove a type of an association
     *
     * @param AssociationInterface $association
     *
     * @return ProductInterface
     */
    public function removeAssociation(AssociationInterface $association);

    /**
     * Get the product association for an Association type
     *
     * @param AssociationTypeInterface $type
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForType(AssociationTypeInterface $type);

    /**
     * Get the product association for an association type code
     *
     * @param string $typeCode
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForTypeCode($typeCode);

    /**
     * Setter for predicate enabled
     *
     * @param bool $enabled
     *
     * @return ProductInterface
     */
    public function setEnabled($enabled);

    /**
     * Predicate to know if product is enabled or not
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Get product completenesses
     *
     * @return Collection of CompletenessInterface
     */
    public function getCompletenesses();

    /**
     * Set product completenesses
     *
     * @param Collection $completenesses
     *
     * @return ProductInterface
     */
    public function setCompletenesses(Collection $completenesses);

    /**
     * Get the attributes of the product
     *
     * @return AttributeInterface[] the attributes of the current product
     */
    public function getAttributes();

    /**
     * Get whether or not an attribute is part of a product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * Get the list of used attribute codes from the indexed values
     *
     * @return array
     */
    public function getUsedAttributeCodes();

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttributeInFamily(AttributeInterface $attribute);

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttributeInVariantGroup(AttributeInterface $attribute);

    /**
     * Check if an attribute can be removed from the product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function isAttributeRemovable(AttributeInterface $attribute);

    /**
     * Check if an attribute can be edited from the product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function isAttributeEditable(AttributeInterface $attribute);

    /**
     * Get product label
     *
     * @param string $locale
     *
     * @return mixed|string
     */
    public function getLabel($locale = null);

    /**
     * Set family
     *
     * @param FamilyInterface $family
     *
     * @return ProductInterface
     */
    public function setFamily(FamilyInterface $family = null);

    /**
     * Get family
     *
     * @return FamilyInterface
     */
    public function getFamily();

    /**
     * Get family id
     *
     * @return int
     */
    public function getFamilyId();

    /**
     * Set family id
     *
     * @param int $familyId
     *
     * @return ProductInterface
     */
    public function setFamilyId($familyId);

    /**
     * @return ArrayCollection
     */
    public function getUniqueData();

    /**
     * @param ProductUniqueDataInterface $uniqueData
     *
     * @return ProductInterface
     */
    public function addUniqueData(ProductUniqueDataInterface $uniqueData);
}
