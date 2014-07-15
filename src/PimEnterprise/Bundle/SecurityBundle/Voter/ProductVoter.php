<?php

namespace PimEnterprise\Bundle\SecurityBundle\Voter;

use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Product voter, allows to know if products can be published, reviewed, edited, consulted by a
 * user depending on his roles
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVoter implements VoterInterface
{
    /**
     * @var CategoryAccessRepository
     */
    protected $categoryAccessRepo;

    /**
     * @param CategoryAccessRepository $categoryAccessRepo
     */
    public function __construct(CategoryAccessRepository $categoryAccessRepo)
    {
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [Attributes::VIEW_PRODUCT, Attributes::EDIT_PRODUCT, Attributes::OWNER]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof ProductInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if ($this->supportsClass($object)) {

            foreach ($attributes as $attribute) {
                if ($this->supportsAttribute($attribute)) {
                    $result = VoterInterface::ACCESS_DENIED;

                    if ($this->isProductAccessible($object, $token->getUser(), $attribute)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                }
            }
        }

        return $result;
    }

    /**
     * Determines if a product is accessible for the user,
     * - no categories : the product is accessible
     * - categories : we apply category's permissions
     *
     * @param ProductInterface $product   the product
     * @param UserInterface    $user      the user
     * @param string           $attribute the attribute
     *
     * @return bool
     */
    protected function isProductAccessible(ProductInterface $product, UserInterface $user, $attribute)
    {
        if (count($product->getCategories()) === 0) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $productToCategory = [
            Attributes::OWNER => Attributes::OWN_PRODUCTS,
            Attributes::EDIT_PRODUCT => Attributes::EDIT_PRODUCTS,
            Attributes::VIEW_PRODUCT => Attributes::VIEW_PRODUCTS,
        ];
        if (!isset($productToCategory[$attribute])) {
            return false;
        }
        $categoryAttribute = $productToCategory[$attribute];

        $categoryIds = [];
        foreach ($product->getCategories() as $category) {
            $categoryIds[] = $category->getId();
        }
        $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $categoryAttribute);

        $intersection = array_intersect($categoryIds, $grantedCategoryIds);
        if (count($intersection)) {
            return true;
        }

        return false;
    }
}
