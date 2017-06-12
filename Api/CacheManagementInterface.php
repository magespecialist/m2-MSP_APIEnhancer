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

namespace MSP\APIEnhancer\Api;

interface CacheManagementInterface
{
    /**
     * Get a cache response or false if not existing
     * @return \Magento\Framework\App\ResponseInterface|false
     */
    public function getCacheResult();

    /**
     * Set a cache response for a request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function setCacheResult(\Magento\Framework\App\ResponseInterface $response);
}
