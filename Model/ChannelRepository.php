<?php

namespace Swissup\Marketplace\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Swissup\Marketplace\Api\ChannelInterface;

class ChannelRepository
{
    /**
     * @var ChannelInterface[]
     */
    private $channels = [];

    /**
     * @param ChannelInterface[] $channels
     */
    public function __construct(
        array $channels
    ) {
        $this->setChannels($channels);
    }

    /**
     * @param ChannelInterface[] $channels
     * @throws AlreadyExistsException
     */
    private function setChannels($channels)
    {
        foreach ($channels as $channel) {
            $identifier = $channel->getIdentifier();

            if (array_key_exists($identifier, $this->channels)) {
                throw new AlreadyExistsException(__('Channel "%1" already exists.', $identifier));
            }

            $this->channels[$identifier] = $channel;
        }

        return $this;
    }

    /**
     * @param string $identifier
     * @param boolean $orFirst
     * @return ChannelInterface
     * @throws NoSuchEntityException
     */
    public function getById($identifier, $orFirst = false)
    {
        if ($identifier) {
            foreach ($this->getList() as $channel) {
                if ($channel->getIdentifier() === $identifier) {
                    return $channel;
                }
            }
        }

        if ($orFirst) {
            foreach ($this->getList(true) as $channel) {
                return $channel;
            }
        }

        throw new NoSuchEntityException(__('Channel "%1" does not exist.', $identifier));
    }

    /**
     * @return ChannelInterface[]
     */
    public function getList($enabledOnly = false)
    {
        if (!$enabledOnly) {
            return $this->channels;
        }

        $result = [];
        foreach ($this->channels as $channel) {
            if (!$channel->isEnabled()) {
                continue;
            }
            $result[] = $channel;
        }

        return $result;
    }
}
