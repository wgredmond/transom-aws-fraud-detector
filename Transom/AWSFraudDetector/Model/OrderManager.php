<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\AWSFraudDetector\Model;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderManager {

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Transom\AWSFraudDetector\Model\ConfigSettings
     */
    protected $config;

    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config) {
        $this->logger = $logger;
        $this->config = $config;
    }


    /**
     * @param $order
     * @param $fraudScore
     */
    public function updateOrderStatus($result, $order) {




    }
//  {
//        $fraudScore = $result['fraud_score'];
//        $riskScore = $result['transaction_details']['risk_score'];
//        $this->logger->info(' ### In updateOrderStatus(); fraudScore = ' . $fraudScore . '; riskScore = ' . $riskScore);
//
//        $outcome = 'legit';
//        $riskDecision = "Order is legit.";
//        if ($riskScore > $this->config->getCancelThreshold()) {
//            $outcome = 'cancel_order';
//        } else if ($riskScore > $this->config->getReviewThreshold()) {
//            $outcome = 'review_order';
//        }
//
//        try {
//            // update order status
//            if ($outcome == 'legit') {
//                $order->addStatusToHistory($order->getStatus(), 'Legit order, IPQS fraud score: ' . $fraudScore . '; Transaction risk score: ' . $riskScore, false);
//            } else if ($outcome == 'review_order') {
//                if ($this->config->isUpdateOrderStatus()) {
//                    $order->setHoldBeforeState($order->getState());
//                    $order->setHoldBeforeStatus($order->getStatus());
//                    $order->setState(Order::STATE_HOLDED);
//                    $order->setStatus(Order::STATUS_FRAUD);
//                    $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and order state to on hold - order should be reviewed.  Transaction risk score: ' . $riskScore . '.  Review Threshold = ' . $this->config->getReviewThreshold() . '; Cancel Threshold = ' . $this->config->getCancelThreshold() . '.  IPQS fraud score: ' . $fraudScore . '; ', false);
//                    $riskDecision = "Order should be reviewed.";
//                } else {
//                    $order->addStatusToHistory($order->getStatus(), '[Update Order Status is disabled] This order would have been placed in review state. Transaction risk score: ' . $riskScore . '.  Review Threshold = ' . $this->config->getReviewThreshold() . '; Cancel Threshold = ' . $this->config->getCancelThreshold() . '.  IPQS fraud score: ' . $fraudScore . '; ', false);
//                    $riskDecision = "[Update Order Status is disabled] Order should be reviewed.";
//                }
//            } else if ($outcome == 'cancel_order') {
//                if ($this->config->isUpdateOrderStatus()) {
//                    $order->setState(Order::STATE_CANCELED);
//                    $order->setStatus(Order::STATUS_FRAUD);
//                    $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and order state cancel.  Transaction risk score: ' . $riskScore . '.  Review Threshold = ' . $this->config->getReviewThreshold() . '; Cancel Threshold = ' . $this->config->getCancelThreshold() . '.  IPQS fraud score: ' . $fraudScore . '; ', false);
//                    $riskDecision = "Order canceled.";
//                } else {
//                    $order->addStatusToHistory($order->getStatus(), '[Update Order Status is disabled] This order has been identified as a fraudulent order and would have been canceled. Transaction risk score: ' . $riskScore . '.  Review Threshold = ' . $this->config->getReviewThreshold() . '; Cancel Threshold = ' . $this->config->getCancelThreshold() . '.  IPQS fraud score: ' . $fraudScore . '; ', false);
//                    $riskDecision = "[Update Order Status is disabled] Order would have been canceled.";
//                }
//            }
//
//            $order->getExtensionAttributes()->setIpqsRiskDecision($riskDecision);
//        } catch (\Throwable $exception) {
//            $this->logger->critical('Exception in updateOrderStatus -- ' . $exception->getMessage());
//            // let order complete
//        }
//    }
}
