<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\PdfGeneratorBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\PdfGeneratorBundle\Controller\ProductController as BaseController;
use Pim\Bundle\PdfGeneratorBundle\Renderer\RendererRegistry;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Product Controller
 *
 * @author Charles Pourcel <charles.pourcel@akeneo.com>
 */
class ProductController extends BaseController
{
    /** @var ProductManager */
    protected $productManager;

    /** @var RendererRegistry */
    protected $rendererRegistry;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param ProductManager           $productManager
     * @param RendererRegistry         $rendererRegistry
     * @param SecurityContextInterface $securityContext
     * @param UserContext              $userContext
     */
    public function __construct(
        ProductManager $productManager,
        RendererRegistry $rendererRegistry,
        SecurityContextInterface $securityContext,
        UserContext $userContext
    ) {
        parent::__construct($productManager, $rendererRegistry);

        $this->securityContext = $securityContext;
        $this->userContext     = $userContext;
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_pdf_generator_product_download")
     */
    public function downloadPdfAction(Request $request, $id)
    {
        $locale = $this->userContext->getCurrentLocale();
        $viewLocaleGranted = $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale);
        if (!$viewLocaleGranted) {
            throw new AccessDeniedException();
        }

        return parent::downloadPdfAction($request, $id);
    }
}