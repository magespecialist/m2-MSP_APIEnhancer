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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use MSP\APIEnhancer\Api\CustomerAuthInterface;

class CustomerAuth implements CustomerAuthInterface
{
    protected $customer = null;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    public function __construct(
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository,
        TokenFactory $tokenFactory
    ) {
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * Get customer by oauth token
     * @return CustomerInterface|false
     */
    public function getCustomer()
    {
        if (is_null($this->customer)) {
            $this->customer = false;

            $authHeader = $this->request->getHeader('Authorization');
            if (preg_match('/^bearer\s+\"?(.+?)\"?\s*$/i', $authHeader, $matches)) {
                $token = $this->tokenFactory->create()->loadByToken($matches[1]);

                if ($token->getId() && !$token->getRevoked() && $token->getCustomerId()) {
                    $customer = $this->customerRepository->getById($token->getCustomerId());

                    $this->customer = $customer;
                }
            }
        }

        return $this->customer;
    }
}
