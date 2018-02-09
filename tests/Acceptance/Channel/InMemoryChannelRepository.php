<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Channel;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\PendingException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

final class InMemoryChannelRepository implements ChannelRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $channels;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->channels->get($code);
    }

    /**
     * {@inheritdoc}
     */
    public function save($channel, array $options = [])
    {
        $this->channels->set($channel->getCode(), $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $channels = [];
        foreach ($this->channels as $locale) {
            foreach ($criteria as $key => $value) {
                $getter = 'get' . ucfirst($key);
                if ($locale->$getter() === $value) {
                    $channels[$locale] = $locale;
                }
            }
        }

        return $channels;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCodes()
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getFullChannels()
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelCountUsingCurrency(CurrencyInterface $currency)
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelsIndexedByCode($localeCode)
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new PendingException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new PendingException(__METHOD__);
    }
}
