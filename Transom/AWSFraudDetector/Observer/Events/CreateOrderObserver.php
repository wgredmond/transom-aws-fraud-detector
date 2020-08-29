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

use DateTime;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Aws\FraudDetector\FraudDetectorClient;
use Transom\AWSFraudDetector\Model\ConfigSettings;


class CreateOrderObserver implements ObserverInterface
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
     * @var DateTime
     */
    protected $eventDate;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;


    /**
     * CreateOrderObserver constructor.
     *
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     * @param DateTime $eventDate
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                DateTime $eventDate,
                                OrderRepositoryInterface $orderRepository,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->eventDate =  $eventDate;
        $this->orderRepository = $orderRepository;
        $this->remoteAddress = $remoteAddress;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // only process order if this service is enable
        if (!$this->config->isApiActive()) {
            return $this;
        }

        // get cretentials, create AWS API client instance
        if ($this->config->isApiAccessKeysInAdmin()) {
            $client = new FraudDetectorClient([
                'version' => 'latest',
                'region' => $this->config->getApiAwsRegion(),
                'credentials' => [
                    'key' => $this->config->getApiIamKey(),
                    'secret' => $this->config->getApiIamSecret()
                ]
            ]);
        } else {
            // TODO get creds from env
            $this->logger->info('TODO hook up dont manage credentals in admin option.');
            return $this;
        }

        // get payment
        $payment = $observer->getData('payment');

        // get order
        $order = $payment->getOrder();

        //  if order data is empty then doesn't need to process
        if (empty($order)) {
            $this->logger->info('There is an error in CreateOrderObserver');
            return $this;
        }

        // populate order and payment variables
        $orderId = $order->getIncrementId();
        $orderAmount = $order->getGrandTotal();
        $orderCurrency = $order->getOrderCurrencyCode();

        // populate customer variables
        $customerId = $order->getCustomerId();
        $customerEmail = $order->getCustomerEmail();

        // populate billing address variables
        $billingAddress = $order->getBillingAddress();
        $billingFirstName = $billingAddress->getFirstname();
        $billingLastName = $billingAddress->getLastName();
        $billingName = $billingFirstName . " " . $billingLastName;
        $billingTelephone = $billingAddress->getTelephone();
        $billingStreet = $billingAddress->getStreet();
        $billingAddress1 = $billingStreet[0];
        $billingAddress2 = "";
        if (isset($billingStreet[1])) {
            $billingAddress2 = $billingStreet[1];
        }
        $billingCity = $billingAddress->getCity();
        $billingRegion = $billingAddress->getRegion();
        $billingCountry = $billingAddress->getCountryId();
        $billingZipCode = $billingAddress->getPostcode();

        // populate shipping address variables
        $shippingAddress = $order->getShippingAddress();
        $shippingFirstName = $shippingAddress->getFirstname();
        $shippingLastName = $shippingAddress->getLastName();
        $shippingName = $shippingFirstName . " " . $shippingLastName;
        $shippingTelephone = $shippingAddress->getTelephone();
        $shippingStreet = $shippingAddress->getStreet();
        $shippingAddress1 = $shippingStreet[0];
        $shippingAddress2 = "";
        if (isset($shippingStreet[1])) {
            $shippingAddress2 = $shippingStreet[1];
        }
        $shippingCity = $shippingAddress->getCity();
        $shippingRegion = $shippingAddress->getRegion();
        $shippingCountry = $shippingAddress->getCountryId();
        $shippingZipCode = $shippingAddress->getPostcode();

        // populate event variables
        $eventTime = $this->eventDate->format('Y-m-d\TH:i:s.') . gettimeofday()['usec'] . 'Z';
        $eventId = $orderId . '-' . $this->eventDate->format('Y-m-d_H-i-s-') . gettimeofday()['usec'];

        // populate session variables
        $ipAddress = $this->remoteAddress->getRemoteAddress();
        //$session            = $this->customerSession->getMyValue();
        $userAgent = $_SERVER ['HTTP_USER_AGENT'];

        // call AWS api for you detector to get create order prediction
        $detectorId = $this->config->getDetectorId();
        try {
            $result = $client->GetEventPrediction([
                'detectorId' => $detectorId,
                'eventId' => 'co-' . $eventId,
                'eventTypeName' => "create_order",
                'eventTimestamp' => $eventTime,
                'entities' => [[
                    'entityType' => 'customer',
                    'entityId' => strval($customerId)
                ]],
                'eventVariables' => [
                    'order_id' => strval($orderId),
                    'user_id' => strval($customerId),
                    'email_address' => $customerEmail,
                    'user_name' => $billingName,

                    'billing_name' => strval($billingName),
                    'billing_address_1' => strval($billingAddress1),
                    'billing_city' => strval($billingCity),
                    'billing_state' => strval($billingRegion),
                    'billing_zip' => strval($billingZipCode),
                    'billing_country' => strval($billingCountry),

                    'shipping_name' => strval($shippingName),
                    'shipping_address_1' => strval($shippingAddress1),
                    'shipping_city' => strval($shippingCity),
                    'shipping_state' => strval($shippingRegion),
                    'shipping_zip' => strval($shippingZipCode),
                    'shipping_country' => strval($shippingCountry),

                    'payment_instrument_type' => 'credit_card',
                    'total_order_price' => strval($orderAmount),
                    'currency_code' => $orderCurrency,

                    'event_timestamp' => $eventTime,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent
                ]
            ]);

            // get AWS score and outcome
            $scoreName = $this->config->getScoreName();
            $modelScores = $result->get('modelScores');
            $ruleResults = $result->get('ruleResults');
            $outcome = 'legit';
            if (!empty($modelScores[0]['scores'][$scoreName])) {
                $insightScore = $modelScores[0]['scores'][$scoreName];
            }
            if (!empty($ruleResults[0]['outcomes'])) {
                $outcome = $ruleResults[0]['outcomes'][0];
            }

            // update order status
            if ($outcome == 'legit') {
                $order->addStatusToHistory($order->getStatus(), 'Legit order, AWS insight score [' . $scoreName . ']: '.$insightScore, false);
            } else if ($outcome == 'block_order') {
                $order->setHoldBeforeState($order->getState());
                $order->setHoldBeforeStatus($order->getStatus());
                $order->setState(Order::STATE_HOLDED);
                $order->setStatus(Order::STATUS_FRAUD);
                $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and state to on hold - for order review.  AWS insight score [' . $scoreName . ']: '.$insightScore, false);
                $this->orderRepository->save($order);
            } else if ($outcome == 'cancel_order') {
                $order->setState(Order::STATE_CANCELED);
                $order->setStatus(Order::STATUS_FRAUD);
                $order->addStatusToHistory(Order::STATUS_FRAUD, 'Setting order status to suspected fraud and state cancel.  AWS insight score [' . $scoreName . ']: '.$insightScore, false);
                $this->orderRepository->save($order);
            }

        } catch (\Throwable $exception) {
            $this->logger->critical('Exception in Transom CreateOrderObserver -- ' . $exception->getMessage());
            // let order complete
        }
    }
}
