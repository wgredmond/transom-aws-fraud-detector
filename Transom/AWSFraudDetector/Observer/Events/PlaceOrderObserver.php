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
use Psr\Log\LoggerInterface;

class PlaceOrderObserver implements ObserverInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('##### In Transom AWSFraudDetector ##### PlaceOrderEvent');

        $order = $observer->getData('order');
        $this->logger->info(' [poe] date type = ' . getType($observer->getData()));
        $this->logger->info(' [poe] event name = ' . $observer->getEvent()->getName());
        foreach ($observer->getData() as $key => $value) {
            $this->logger->info(' [poe] data[' . $key . '] type = ' . getType($value));
            if (getType($value) === 'object') {
                $this->logger->info(' [poe] object type = ' . get_class($value));
            }
        }
        foreach ($observer->getData() as $key => $value) {
            $this->logger->info(' [poe] data[' . $key . '] type = ' . getType($value));
            if (getType($value) === 'object') {
                $this->logger->info(' [poe] object type = ' . get_class($value));
            }
        }

//        // get payment and order
//        $payment = $observer->getData('payment');
//        $order = $payment->getOrder();
//
//        if ($payment !== null) {
//            $this->logger->info(' [po] payment id = ' . $payment->getEntityId());
//            $this->logger->info(' [po] payment amount authorized = ' . $payment->getAmountAuthorized());
//            $this->logger->info(' [po] payment cc type = ' . $payment->getCcType());
//            $this->logger->info(' [po] payment cc last 4 = ' . $payment->getCcLast4());
//        }
//
//        if ($order !== null) {
//            $this->logger->info(' [po] order id = ' . $order->getEntityId());
//        }

    }
}
