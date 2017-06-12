<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_APIEnhancer
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\APIEnhancer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\PageCache\Model\Config as PageCacheConfig;
use MSP\APIEnhancer\Api\TagInterface;
use MSP\APIEnhancer\Api\VarnishManagementInterface;
use MSP\APIEnhancer\Api\VarnishTokenProcessorInterface;

class VarnishManagement implements VarnishManagementInterface
{
    const SECRET_KEY = 'msp_apienhancer/secret/key';

    /**
     * @var array
     */
    private $keys;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var PageCacheConfig
     */
    private $pageCacheConfig;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TagInterface
     */
    private $tag;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        TokenFactory $tokenFactory,
        CustomerRepositoryInterface $customerRepository,
        PageCacheConfig $pageCacheConfig,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        TagInterface $tag,
        $keys = []
    ) {
        $this->keys = $keys;
        $this->tokenFactory = $tokenFactory;
        $this->customerRepository = $customerRepository;
        $this->pageCacheConfig = $pageCacheConfig;
        $this->dateTime = $dateTime;
        $this->tag = $tag;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get new token with Vary information
     * @param string $originalToken
     * @return string
     */
    public function getVaryToken($originalToken)
    {
        $token = $this->tokenFactory->create()->loadByToken($originalToken);
        $customer = $this->customerRepository->getById($token->getCustomerId());

        $keyInfo = [];
        foreach ($this->keys as $code => $keyProcessor) {
            /** @var $keyProcessor VarnishTokenProcessorInterface */
            $keyInfo = array_merge($keyInfo, $keyProcessor->getKeys($token, $customer));
        }

        $secretKey = $this->scopeConfig->getValue(static::SECRET_KEY);
        $keyInfo[] = $secretKey;

        return md5(serialize($keyInfo)).':'.$originalToken;
    }

    /**
     * Inject varnish cache headers in response
     * @param \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function setVarnishHeaders(\Magento\Framework\App\ResponseInterface $response)
    {
        $tags = $this->tag->getTags();
        if (!count($tags)) {
            return;
        }

        $ttl = $this->pageCacheConfig->getTtl();

        $expireTs = $this->dateTime->gmtDate('D, d M Y H:i:s \G\M\T', $this->dateTime->gmtTimestamp() + $ttl);
        $response->setHeader('X-Magento-Tags', implode(',', $tags));
        $response->setHeader('Pragma', 'cache');
        $response->setHeader('Cache-Control', 'max-age=' . $ttl . ', public, s-maxage=' . $ttl);
        $response->setHeader('Expires', $expireTs);
    }
}
