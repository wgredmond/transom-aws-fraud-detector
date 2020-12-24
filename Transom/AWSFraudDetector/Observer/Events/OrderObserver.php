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
use Transom\AWSFraudDetector\Helper\Api;
use Transom\AWSFraudDetector\Model\ConfigSettings;


class OrderObserver implements ObserverInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Transom\AWSFraudDetector\Model\ConfigSettings
     */
    protected $config;

    /**
     * @var \Transom\AWSFraudDetector\Helper\Api
     */
    protected $api;


    /**
     * CreateAccountObserver constructor.
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     * @param Api $api
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                Api $api)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->api = $api;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$localDebug = true;
        $localDebug = false;

        $eventName = $observer->getEvent()->getName();

        if ($localDebug) {
            $this->logger->info('[AWSFraudDetector] OrderObserver BEGIN [' . microtime() . ']');
        }
        // only process order if this service is enable
        if (!$this->config->isApiActive()) {
            if ($localDebug) {
                $this->logger->info('[AWSFraudDetector] OrderObserver END [' . microtime() . ']');
            }
            return $this;
        }


        // TODO - start debug
        if ($localDebug) {
            $this->logger->info('[AWSFraudDetector] [' . $eventName . '] data type = ' . getType($observer->getData()));
            $this->logger->info('[AWSFraudDetector] [' . $eventName . '] event name = ' . $observer->getEvent()->getName());
            foreach ($observer->getData() as $key => $value) {
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] data[' . $key . '] type = ' . getType($value));
                if (getType($value) === 'object') {
                    $this->logger->info('[AWSFraudDetector] [' . $eventName . '] object type = ' . get_class($value));
                }
            }
        }
        // TODO - end debug


        // get payment
        if ( ($eventName === 'sales_order_payment_place_end') OR ($eventName === 'sales_order_payment_place_start') ){
            $payment = $observer->getData('payment');

            // get order
            $order = $payment->getOrder();
        } else {
            $payment = null;
            $order = $observer->getData('order');
        }

        // TODO - start debug - order
        if ($localDebug) {
            if ($order) {
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] order class = ' . get_class($order));
                foreach ($order->getData() as $key => $value) {
                    if (is_array($value) || (getType($value) === 'object')) {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] order data[' . $key . '] type = ' . getType($value));
                    } else {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] order data[' . $key . '] type = ' . getType($value) . ';value is: ' . $value);
                    }
                    if (getType($value) === 'object') {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] order object type = ' . get_class($value));
                    }
                }
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] order; id = ' . $order->getId());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] order; entity id = ' . $order->getEntityId());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] order; increment id = ' . $order->getIncrementId());
            }
        }
        // TODO - end debug - order

        // TODO - start debug - payment
        if ($localDebug) {
            if ($payment) {
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] payment class = ' . get_class($payment));

                foreach ($payment->getData() as $key => $value) {
                    if (is_array($value) || (getType($value) === 'object')) {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] payment data[' . $key . '] type = ' . getType($value));
                    } else {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] payment data[' . $key . '] type = ' . getType($value) . ';value is: ' . $value);
                    }
                    if (getType($value) === 'object') {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] payment object type = ' . get_class($value));
                    }
                }

                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; entity id = ' . $payment->getEntityId());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; amount authorized = ' . $payment->getAmountAuthorized());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; method = ' . $payment->getMehtod());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; cc type = ' . $payment->getCcType());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; cc cid status = ' . $payment->getCcCidStatus());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; cc status = ' . $payment->getCcStatus());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; cc trans id = ' . $payment->getCcTransId());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; cc last 4 = ' . $payment->getCcLast4());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; cc exp month = ' . $payment->getCcExpMonth());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; cc exp year = ' . $payment->getCcExpYear());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; parent id = ' . $payment->getParentId());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; transaction id = ' . $payment->getTransactionId());
                $this->logger->info('[AWSFraudDetector] [' . $eventName . '] processPayment; can capture = ' . $payment->canCapture());
                if ($payment->getAdditionalInformation()) {
                    //$this->logger->info('[AWSFraudDetector] [' . $eventName . '] payment additional information class = ' . get_class($payment->getAdditionalInformation()));
                    foreach ($payment->getAdditionalInformation() as $key => $value) {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] additional information [' . $key . '] type = ' . getType($value));
                        if (getType($value) === 'object') {
                            $this->logger->info('[AWSFraudDetector] [' . $eventName . '] additional information object type = ' . get_class($value));
                        }
                    }
                }
                if ($payment->getTransactionAdditionalInfo()) {
                    $this->logger->info('[AWSFraudDetector] [' . $eventName . '] payment transaction additional information class = ' . get_class($payment->getTransactionAdditionalInfo()));
                    foreach ($payment->getTransactionAdditionalInfo() as $key => $value) {
                        $this->logger->info('[AWSFraudDetector] [' . $eventName . '] transaction additional information [' . $key . '] type = ' . getType($value));
                        if (getType($value) === 'object') {
                            $this->logger->info('[AWSFraudDetector] [' . $eventName . '] transaction additional information object type = ' . get_class($value));
                        }
                    }
                }
            }
        }
        // TODO - end debug - payment


        //  if order data is empty then doesn't need to process
        if (empty($order)) {
            return $this;
        }

        if ($eventName === 'sales_order_payment_place_end') {
            $this->api->sendTransaction($order, $payment);
        }

        //
        // Recoverable Error: Object of class Magento\Sales\Api\Data\OrderExtension could not be converted to string in
        // /var/www/vhosts/dev.m2.local.com/app/code/Transom/AWSFraudDetector/Observer/Events/OrderObserver.php on line 185
        //

        if ($eventName === 'sales_order_save_after') {
            $extAttribs = $order->getExtensionAttributes();
            if (!empty($extAttribs->getAwsOutcome())) {
                $this->api->saveOrderScore($order->getId(), $extAttribs->getAwsInsightScore(), $extAttribs->getAwsOutcome());
            }
        }

        if ($localDebug) {
            $this->logger->info('[AWSFraudDetector] OrderObserver END [' . microtime() . ']');
        }
    }

}
