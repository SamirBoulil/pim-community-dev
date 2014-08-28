<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;

/**
 * Present changes on option data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 */
class OptionPresenter extends AbstractProductValuePresenter
{
    /** @var AttributeOptionRepository */
    protected $repository;

    /**
     * @param AttributeOptionRepository $repository
     */
    public function __construct(AttributeOptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('option', $change);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return (string) $this->repository->find($change['option']);
    }
}
