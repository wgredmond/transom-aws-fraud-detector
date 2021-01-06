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
    public function updateOrderStatus($insightScore, $outcome, $scoreName, $order) {
        $localDebug = true;
        //$localDebug = false;

        if ($localDebug) {
            $this->logger->info(' ### In updateOrderStatus(); outcome = ' . $outcome);
        }

        $thresholdInfo = ' Review Threshold = ' . $this->config->getReviewThreshold() . '; Cancel Threshold = ' . $this->config->getCancelThreshold() . '.';

        // update order status
        if ($outcome == $this->config->getOutcomeLegit()) {
            if ($localDebug) {
                $this->logger->info(' ### In updateOrderStatus(); Order is legit.');
            }
            $order->addStatusToHistory($order->getStatus(), 'Legit order, AWS insight score [' . $scoreName . ']: '.$insightScore, false);
        } else if ($outcome == $this->config->getOutcomeReview()) {
            if ($this->config->isUpdateOrderStatus()) {
                if ($localDebug) {
                    $this->logger->info(' ### In updateOrderStatus(); Order is under review. Updating order status and adding status note.');
                }
                $order->setHoldBeforeState($order->getState());
                $order->setHoldBeforeStatus($order->getStatus());
                $order->setState(Order::STATE_HOLDED);
                $order->setStatus(Order::STATUS_FRAUD);
                $order->addStatusToHistory(Order::STATUS_FRAUD, 'Order is under review. Setting order status to suspected fraud and state to on hold.  AWS insight score [' . $scoreName . ']: ' . $insightScore . ';' . $thresholdInfo, false);
            } else {
                if ($localDebug) {
                    $this->logger->info(' ### In updateOrderStatus(); Order would be under review. Only adding status note.');
                }
                $order->addStatusToHistory($order->getStatus(), '[Order status update is disabled] Order would be under review.  AWS insight score [' . $scoreName . ']: ' . $insightScore . ';' . $thresholdInfo, false);
            }
        } else if ($outcome == $this->config->getOutcomeCancel()) {
            if ($this->config->isUpdateOrderStatus()) {
                if ($localDebug) {
                    $this->logger->info(' ### In updateOrderStatus(); Order is cacelled. Updating order status and adding status note.');
                }
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus(Order::STATUS_FRAUD);
                $order->addStatusToHistory(Order::STATUS_FRAUD, 'Order is cacelled. Setting order status to suspected fraud and state cancel.  AWS insight score [' . $scoreName . ']: ' . $insightScore . ';' . $thresholdInfo, false);
            } else {
                if ($localDebug) {
                    $this->logger->info(' ### In updateOrderStatus(); Order would have been cancelled. Only adding status note.');
                }
                $order->addStatusToHistory($order->getStatus(), '[Order status update is disabled] Order would have been cancelled.  AWS insight score [' . $scoreName . ']: ' . $insightScore . ';' . $thresholdInfo, false);
            }
        }
    }
}
