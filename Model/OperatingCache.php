<?php

namespace Esto\HirePurchase\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Esto\HirePurchase\Model\Cache\Type;
use Magento\Store\Model\StoreManagerInterface;

class OperatingCache
{
    private $cacheData = [];

    private $serializer;

    private $cache;

    private $storeManager;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CacheInterface      $cache,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager
    )
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    public function saveCache()
    {
        $cacheKey = Type::TYPE_IDENTIFIER;
        $cacheTag = Type::CACHE_TAG;
        $storeId = $this->storeManager->getStore()->getId();
        $cacheData = [$storeId => $this->cacheData];

        $this->cache->save(
            $this->serializer->serialize($cacheData),
            $cacheKey,
            [$cacheTag]
        );
    }

    public function loadCache()
    {
        $cacheKey = Type::TYPE_IDENTIFIER;
        $cached = $this->cache->load($cacheKey);
        if ($cached) {
            $storeId = $this->storeManager->getStore()->getId();
            $cacheData = $this->serializer->unserialize(
                $cached
            );
            $this->cacheData = $cacheData[$storeId] ?? [];
        }
        return $this;
    }

    public function checkPrice($price)
    {
        if (empty($this->cacheData)) {
            $this->loadCache();
        }

        return $this->cacheData[(string)$price] ?? false;
    }

    public function getPrice($price)
    {
        if (empty($this->cacheData)) {
            $this->loadCache();
        }
        return $this->cacheData[(string)$price] ?? false;
    }

    public function setPrice($price, $amount)
    {
        if (empty($this->cacheData)) {
            $this->loadCache();
        }
        return $this->cacheData[(string)$price] = $amount;
    }
}
