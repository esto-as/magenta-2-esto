<?php
namespace Esto\HirePurchase\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class Type extends TagScope
{
    /**
     * Type Code for Cache
     */
    const TYPE_IDENTIFIER = 'estohirepurchase';

    /**
     * Tag of Cache
     */
    const CACHE_TAG = 'ESTOCACHE';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(
        FrontendPool $cacheFrontendPool
    ){
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }
}
