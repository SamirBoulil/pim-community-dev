<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\View;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater\DraftViewUpdater;
use PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater\SmartViewUpdater;
use PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterInterface;
use Symfony\Component\Form\FormView;

/**
 * Product form view decorator that adds classes and fields
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class HighlightProductFormView implements ProductFormViewInterface
{
    /** @var ProductFormViewInterface */
    protected $productFormView;

    /** @var DraftViewUpdater */
    protected $draftViewUpdater;

    /** @var SmartViewUpdater  */
    protected $smartViewUpdater;

    /**
     * @param ProductFormViewInterface $productFormView
     * @param ViewUpdaterInterface     $draftViewUpdater
     * @param ViewUpdaterInterface     $smartViewUpdater
     */
    public function __construct(
        ProductFormViewInterface  $productFormView,
        ViewUpdaterInterface $draftViewUpdater,
        ViewUpdaterInterface $smartViewUpdater
    ) {
        $this->productFormView  = $productFormView;
        $this->draftViewUpdater = $draftViewUpdater;
        $this->smartViewUpdater = $smartViewUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function addChildren(ProductValueInterface $value, FormView $view)
    {
        $this->productFormView->addChildren($value, $view);
    }

    /**
     * {@inheritdoc}
     */
    public function getView()
    {
        $views = $this->productFormView->getView();

        foreach ($views as $key => $view) {
            foreach (array_keys($view['attributes']) as $name) {
                $this->draftViewUpdater->update($views, $key, $name);
                $this->smartViewUpdater->update($views, $key, $name);
            }
        }

        return $views;
    }
}
