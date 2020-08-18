<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\AWSFraudDetector\Plugin\Sales\Api;

use Psr\Log\LoggerInterface;

class OrderManagementInterfacePlugin {

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function aroundPlace(\Magento\Sales\Model\Service\OrderService $subject, \Closure $proceed,  \Magento\Sales\Api\Data\OrderInterface $order)
    //public function aroundPlace(OrderManagementInterface $subject, callable $proceed, OrderInterface $order)
    {
        $exception = null;
        $transactionSuccess = true;

        //$orderId = $order->getEntityId();
        $this->logger->info('##### In Transom AWSFraudDetector ##### OrderManagementInterfacePlugin:aroundPlace 1; orderId (entity) = ' . $order->getEntityId());
        $this->logger->info('##### In Transom AWSFraudDetector ##### OrderManagementInterfacePlugin:aroundPlace 1; orderId (id) = ' . $order->getId());
        $this->logger->info('##### In Transom AWSFraudDetector ##### OrderManagementInterfacePlugin:aroundPlace 1; orderId (incremental) = ' . $order->getIncrementId());

        try {
            $return = $proceed($order);
        } catch (\Throwable $exception) {
            $transactionSuccess = false;
        }

//        $this->logger->info(' order get payment? ' . $order->getPayment() !== null);
//        if ($order->getPayment() !== null) {
//            $this->logger->info(' order get payment has instance method? ' . $order->getPayment()->hasInstanceMethod());
//        }
        $this->logger->info('transaction success? ' . $transactionSuccess);

        if ($order->getPayment() !== null) {
            $this->logger->info(' [omip] payment id = ' . $order->getPayment()->getEntityId());
            $this->logger->info(' [omip] payment cc type = ' . $order->getPayment()->getCcType());
        }

//        if ($order->getPayment() !== null && $order->getPayment()->hasInstanceMethod()) {
//            $this->logger->info(' payment id = ' . $order->getPayment()->getEntityId());
//            $this->logger->info(' payment cc type = ' . $order->getPayment()->getCcType());
//        }

        $this->logger->info('##### In Transom AWSFraudDetector ##### OrderManagementInterfacePlugin:aroundPlace 2; orderId (entity) = ' . $order->getEntityId());
        $this->logger->info('##### In Transom AWSFraudDetector ##### OrderManagementInterfacePlugin:aroundPlace 2; orderId (id) = ' . $order->getId());
        $this->logger->info('##### In Transom AWSFraudDetector ##### OrderManagementInterfacePlugin:aroundPlace 2; orderId (incremental) = ' . $order->getIncrementId());

        if ($exception !== null) {
            throw $exception;
        }
        return $return;
    }
}

