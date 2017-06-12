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

use MSP\APIEnhancer\Api\CacheManagementInterface;
use Magento\Framework\Webapi\Rest\Response;
use MSP\APIEnhancer\Api\CacheKeyProcessorInterface;
use MSP\APIEnhancer\Api\TagInterface;
use Zend\Http\Headers;


class CacheManagement implements CacheManagementInterface
{
    const CACHE_TTL = 86400;

    /**
     * @var CacheType
     */
    private $cacheType;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var TagInterface
     */
    private $tag;


    public function __construct(
        CacheType $cacheType,
        Response $response,
        TagInterface $tag,
        array $keys = []
    ) {
        $this->keys = $keys;
        $this->tag = $tag;
        $this->response = $response;
        $this->cacheType = $cacheType;
    }

    /**
     * Get cache key from request
     * @return string
     */
    protected function getCacheKey()
    {
        $keyInfo = [];
        foreach ($this->keys as $code => $keyProcessor) {
            /** @var $keyProcessor CacheKeyProcessorInterface */
            $keyInfo = array_merge($keyInfo, $keyProcessor->getKeys());
        }

        return md5(serialize($keyInfo));
    }

    /**
     * Get a cache response or false if not existing
     * @return \Magento\Framework\App\ResponseInterface|false
     */
    public function getCacheResult()
    {
        $cacheKey = $this->getCacheKey();

        if (!$this->cacheType->test($cacheKey)) {
            return false;
        }

        $cacheData = unserialize($this->cacheType->load($cacheKey));

        $this->response->setHttpResponseCode($cacheData['code']);
        $this->response->setHeaders(Headers::fromString($cacheData['headers']));
        $this->response->setBody($cacheData['body']);

        return $this->response;
    }

    /**
     * Set a cache response for a request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function setCacheResult(\Magento\Framework\App\ResponseInterface $response)
    {
        $cacheKey = $this->getCacheKey();

        $tags = $this->tag->getTags();
        if (!count($tags)) {
            return;
        }

        $responseBody = $response->getBody();
        $responseCode = $response->getStatusCode();
        $responseHeaders = $response->getHeaders()->toString();
        $cacheData = [
            'code' => $responseCode,
            'headers' => $responseHeaders,
            'body' => $responseBody,
        ];

        $this->cacheType->save(serialize($cacheData), $cacheKey, $tags, static::CACHE_TTL);
    }
}
