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

namespace MSP\APIEnhancer\Model\VarnishTokenProcessor;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use MSP\APIEnhancer\Api\CacheKeyProcessorInterface;
use MSP\APIEnhancer\Api\VarnishTokenProcessorInterface;

class Group implements VarnishTokenProcessorInterface
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        TokenFactory $tokenFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Return a list of cache keys for a request
     * @param string $token
     * @param CustomerInterface $customer
     * @return array
     */
    public function getKeys($token, CustomerInterface $customer)
    {
        if ($customer && $customer->getId()) {
            return [$customer->getGroupId()];
        }

        return [0];
    }
}
