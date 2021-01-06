<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\AWSFraudDetector\Observer\Events;

use Magento\Framework\Event\ObserverInterface;
use Transom\AWSFraudDetector\Helper\Api;


class AdminConfigChange implements ObserverInterface
{

    /**
     * @var \Transom\AWSFraudDetector\Helper\Api
     */
    protected $api;

    /**
     * CreateAccountObserver constructor.
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        if ($eventName === 'admin_system_config_changed_section_trust_and_safety') {
            $this->api->updateRule();
        }
    }
}
