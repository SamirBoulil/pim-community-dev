<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;

/**
 * Product flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDenormalizer extends AbstractEntityDenormalizer
{
    /** @staticvar string */
    const FIELD_ENABLED      = 'enabled';

    /** @staticvar string */
    const FIELD_FAMILY       = 'family';

    /** @staticvar string */
    const FIELD_CATEGORIES   = 'categories';

    /** @staticvar string */
    const FIELD_GROUPS       = 'groups';

    /** @var string */
    protected $fieldNameBuilder;

    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ManagerRegistry  $managerRegistry
     * @param string           $entityClass
     * @param ProductBuilder   $productBuilder
     * @param FieldNameBuilder $fieldNameBuilder
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $entityClass,
        ProductBuilder $productBuilder,
        FieldNameBuilder $fieldNameBuilder
    ) {
        parent::__construct($managerRegistry, $entityClass);

        $this->productBuilder    = $productBuilder;
        $this->fieldNameBuilder  = $fieldNameBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        /** @var ProductInterface $product */
        $product = $this->getEntity($data, $context);

        if (isset($data[self::FIELD_ENABLED])) {
            $product->setEnabled((bool) $data[self::FIELD_ENABLED]);
            unset($data[self::FIELD_ENABLED]);
        }

        if (isset($data[self::FIELD_FAMILY])) {
            $this->denormalizeFamily($data[self::FIELD_FAMILY], $format, [], $product);
            unset($data[self::FIELD_FAMILY]);
        }

        if (isset($data[self::FIELD_CATEGORIES])) {
            $this->denormalizeCategories($data[self::FIELD_CATEGORIES], $format, [], $product);
            unset($data[self::FIELD_CATEGORIES]);
        }

        if (isset($data[self::FIELD_GROUPS])) {
            $this->denormalizeGroups($data[self::FIELD_GROUPS], $format, [], $product);
            unset($data[self::FIELD_GROUPS]);
        }

        $this->denormalizeAssociations($data, $format, [], $product);

        $this->denormalizeValues($data, $format, [], $product);

        return $product;
    }

    /**
     * Denormalize the product family
     *
     * @param string           $data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeFamily($data, $format, array $context = array(), ProductInterface $product)
    {
        if (strlen($data) > 0) {
            $family = $this->serializer->denormalize($data, $this->getTargetClass('family'), $format, $context);
        } else {
            $family = null;
        }

        $product->setFamily($family);
    }

    /**
     * Denormalize product categories
     *
     * @param string           $data
     * @param string           $format
     * @oaram array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeCategories($data, $format, array $context = array(), ProductInterface $product)
    {
        // Remove existing categories
        foreach ($product->getCategories() as $category) {
            $product->removeCategory($category);
        }

        // Adding categories
        $categoryClass = $this->getTargetClass('categories');
        $categoryCodes = strlen($data) > 0 ? explode(",", $data) : array();
        foreach ($categoryCodes as $categoryCode) {
            $product->addCategory(
                $this->serializer->denormalize($categoryCode, $categoryClass, $format, $context)
            );
        }
    }

    /**
     * Denormalize product groups
     *
     * @param string           $data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeGroups($data, $format, array $context = array(), ProductInterface $product)
    {
        // Remove existing groups
        foreach ($product->getGroups() as $group) {
            $product->removeGroup($group);
        }

        // Adding groups
        $groupClass = $this->getTargetClass('groups');
        $groupCodes = strlen($data) > 0 ? explode(",", $data) : array();
        foreach ($groupCodes as $groupCode) {
            $product->addGroup(
                $this->serializer->denormalize($groupCode, $groupClass, $format, $context)
            );
        }
    }

    /**
     * Denormalize product associations
     *
     * @param string           &$data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeAssociations(&$data, $format, array $context = array(), ProductInterface $product)
    {
        // Remove existing associations
        foreach ($product->getAssociations() as $association) {
            foreach ($association->getGroups() as $group) {
                $association->removeGroup($group);
            }

            foreach ($association->getProducts() as $prod) {
                $association->removeProduct($prod);
            }
        }

        // Get association field names and add associations
        $associationClass = $this->getTargetClass('associations');
        $assocFieldNames  = $this->fieldNameBuilder->getAssociationFieldNames();
        foreach ($assocFieldNames as $assocFieldName) {
            if (isset($data[$assocFieldName])) {
                list($associationTypeCode, $part) = explode('-', $assocFieldName);

                $association = $product->getAssociationForTypeCode($associationTypeCode);
                $association = $this->serializer->denormalize(
                    $data[$assocFieldName],
                    $associationClass,
                    $format,
                    ['entity' => $association, 'association_type_code' => $associationTypeCode, 'part' => $part]
                );

                if (!$product->getAssociationForTypeCode($associationTypeCode)) {
                    $product->addAssociation($association);
                }

                unset($data[$assocFieldName]);
            }
        }
    }

    /**
     * Denormalize product values
     *
     * @param string           $data
     * @param string           $format
     * @param array            $context
     * @param ProductInterface $product
     */
    protected function denormalizeValues($data, $format, array $context = array(), ProductInterface $product)
    {
        // Remove existing values
        foreach ($product->getValues() as $value) {
            $product->removeValue($value);
        }

        $valueClass = $this->getTargetClass('values');
        foreach ($data as $attFieldName => $dataValue) {
            $attributeInfos = $this->fieldNameBuilder->extractAttributeFieldNameInfos($attFieldName);
            $attribute = $attributeInfos['attribute'];
            unset($attributeInfos['attribute']);

            // Add attribute to product if not already done
            if (!$product->hasAttribute($attribute)) {
                $this->productBuilder->addAttributeToProduct($product, $attribute);
            }

            // Denormalize data value.
            // The value is already added to the product so automatically updated
            $productValue = $product->getValue(
                $attribute->getCode(),
                $attributeInfos['locale_code'],
                $attributeInfos['scope_code']
            );
            $this->serializer->denormalize(
                $dataValue,
                $valueClass,
                $format,
                [
                    'product' => $product,
                    'entity'  => $productValue
                ] + $attributeInfos
            );
        }
    }
}
