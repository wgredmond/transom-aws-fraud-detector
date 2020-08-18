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
use Psr\Log\LoggerInterface;
use Aws\FraudDetector\FraudDetectorClient;


class CreateOrderEvent implements ObserverInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DateTime
     */
    protected $eventDate;


    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    public function __construct(LoggerInterface $logger,
                                DateTime $eventDate,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->eventDate =  $eventDate;
        $this->remoteAddress = $remoteAddress;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('##### In Transom AWSFraudDetector ##### CreateOrderEvent');
        $this->logger->info('0.2');

        $this->logger->info(' [coe] date type = ' . getType($observer->getData()));
        $this->logger->info(' [coe] event name = ' . $observer->getEvent()->getName());
        foreach ($observer->getData() as $key => $value) {
            $this->logger->info(' [coe] data[' . $key . '] type = ' . getType($value));
            if (getType($value) === 'object') {
                $this->logger->info(' [coe] object type = ' . get_class($value));
            }
        }
        /*
         * admin
         * AKIAVBHZLHZR72NGUNPH
         * CIv+STPEq36gmRUi8ACLKeXtibbFnFfyGzYgD79P
         */
        $client = new FraudDetectorClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => [
                'key'    => 'AKIAVBHZLHZR72NGUNPH',
                'secret' => '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'
            ]
        ]);
        $this->logger->info('1');
        //$this->logger->info($client);

//        //'detectorId' => 'crs_demo_order', // REQUIRED
//        $detectors_result = $client->GetDetectors([
//            'maxResults' => 10
//        ]);
//        $this->logger->info('2');
//        $this->logger->info($detectors_result);

        // get order
        $order = $observer->getEvent()->getOrder();
        $this->logger->info('3.1');

        // If order data is empty then doesn't need to process
//        if (empty($order)) {
//            $this->logger->info('There is an error in CreateOrderEvent');
//            return $this;
//        }
//        $this->logger->info('3.2');

        //Order main info
        $orderId           = $order->getIncrementId();
        $orderAmount       = $order->getGrandTotal();
        $orderCurrency     = $order->getOrderCurrencyCode();
        $this->logger->info('3.3 -- orderId = ' . $orderId);
        $this->logger->info('3.3 -- orderAmount = ' . $orderAmount);
        $this->logger->info('3.3 -- orderCurrency = ' . $orderCurrency);
        $this->logger->info('3.3 -- $orderId = ' . $orderId);

        //Customer main info
        $customerId        = $order->getCustomerId();
        $customerEmail     = $order->getCustomerEmail();
        //$session            = $this->customerSession->getMyValue();
        $userAgent     = $_SERVER ['HTTP_USER_AGENT'];
        $this->logger->info('3.4 -- customerId = ' . $customerId);
        $this->logger->info('3.4 -- customerEmail = ' . $customerEmail);

        //Billing Address details
        $billingAddress     = $order->getBillingAddress();
        $billingFirstName   = $billingAddress->getFirstname();
        $billingLastName    = $billingAddress->getLastName();
        $billingName        = $billingFirstName." ".$billingLastName;
        $billingTelephone   = $billingAddress->getTelephone();
        $billingStreet      = $billingAddress->getStreet();
        $billingAddress1    = $billingStreet[0];
        $billingAddress2    = "";
        if(isset($billingStreet[1])){
            $billingAddress2 = $billingStreet[1];
        }
        $billingCity        = $billingAddress->getCity();
        $billingRegion      = $billingAddress->getRegion();
        $billingCountry     = $billingAddress->getCountryId();
        $billingZipCode     = $billingAddress->getPostcode();
        $this->logger->info('3.5 -- billingName = ' . $billingName);
        $this->logger->info('3.5 -- billingZipCode = ' . $billingZipCode);

        //Shipping Address details
        $shippingAddress    = $order->getShippingAddress();
        $shippingFirstName  = $shippingAddress->getFirstname();
        $shippingLastName   = $shippingAddress->getLastName();
        $shippingName       = $shippingFirstName." ".$shippingLastName;
        $shippingTelephone  = $shippingAddress->getTelephone();
        $shippingStreet     = $shippingAddress->getStreet();
        $shippingAddress1   = $shippingStreet[0];
        $shippingAddress2   = "";
        if(isset($shippingStreet[1])){
            $shippingAddress2 = $shippingStreet[1];
        }
        $shippingCity       = $shippingAddress->getCity();
        $shippingRegion     = $shippingAddress->getRegion();
        $shippingCountry    = $shippingAddress->getCountryId();
        $shippingZipCode    = $shippingAddress->getPostcode();
        $this->logger->info('3.6 -- shippingName = ' . $shippingName);
        $this->logger->info('3.6 -- shippingZipCode = ' . $shippingZipCode);

        $eventTime = $this->eventDate->format('Y-m-d\TH:i:s.').gettimeofday()['usec'] . 'Z';
        $eventId = $orderId . '-' . $this->eventDate->format('Y-m-d_H-i-s-').gettimeofday()['usec'];

        $this->logger->info('3.7 -- eventTime = ' . $eventTime);
        $this->logger->info('3.7 -- eventId = ' . $eventId);

        $ipAddress = $this->remoteAddress->getRemoteAddress();
        $this->logger->info('3.8 -- ipAddress = ' . $ipAddress);
        $this->logger->info('3.8 -- userAgent = ' . $userAgent);

//        $result = $client->GetEventPrediction([
//            'detectorId' => 'fraud_order',
//            'eventId' => 'fo-' . $eventId,
//            'eventTypeName' => "create_order",
//            'eventTimestamp'    => $eventTime,
//            'entities' => [[
//                'entityType'    => 'customer',
//                'entityId'      => $customerId
//            ]],
//            'eventVariables' => [
//                'order_id'          => $orderId,
//                'user_id'           => $customerId,
//                'email_address'     => $customerEmail,
//                'user_name'         => $billingName,
//
//                'billing_name'         => $billingName,
//                'billing_address_1'    => $billingAddress1,
//                'billing_city'         => $billingCity,
//                'billing_state'        => $billingRegion,
//                'billing_zip'          => $billingZipCode,
//                'billing_country'      => $billingCountry,
//                'billing_phone_number' => $billingTelephone,
//
//                'shipping_name'         => $shippingName,
//                'shipping_address_1'    => $shippingAddress1,
//                'shipping_city'         => $shippingCity,
//                'shipping_state'        => $shippingRegion,
//                'shipping_zip'          => $shippingZipCode,
//                'shipping_country'      => $shippingCountry,
//                'shipping_phone_number' => $shippingTelephone,
//
//                'payment_instrument_type'  => 'credit_card',
////                //'credit_card_type'        => '...',
//                'total_order_price'        => strval($orderAmount),
//                'currency_code'            => $orderCurrency,
//
//                'event_timestamp' => $eventTime,
//                'ip_address'      => $ipAddress,
//                'user_agent'      => $userAgent
//            ]
//        ]);
//        $this->logger->info('3.9.0 -- fraud_order');
//        $this->logger->info($result);

//        $this->logger->info('3.9.1.0.1 -- crs_demo_order');
//
//        $result = $client->GetEventPrediction([
//            'detectorId' => 'create_order_detector',
//            'eventId' => 'crs-' . $eventId,
//            'eventTypeName' => "create_order",
//            'eventTimestamp'    => $eventTime,
//            'entities' => [[
//                'entityType'    => 'customer',
//                'entityId'      => strval($customerId)
//            ]],
//            'eventVariables' => [
//                'order_id'          => strval($orderId),
//                'user_id'           => strval($customerId),
//                'email_address'     => $customerEmail,
//                'user_name'         => $billingName,
//
//                'billing_name'         => strval($billingName),
//                'billing_address_1'    => strval($billingAddress1),
//                'billing_city'         => strval($billingCity),
//                'billing_state'        => strval($billingRegion),
//                'billing_zip'          => strval($billingZipCode),
//                'billing_country'      => strval($billingCountry),
//                'billing_phone_number' => strval($billingTelephone),
//
//                'shipping_name'         => strval($shippingName),
//                'shipping_address_1'    => strval($shippingAddress1),
//                'shipping_city'         => strval($shippingCity),
//                'shipping_state'        => strval($shippingRegion),
//                'shipping_zip'          => strval($shippingZipCode),
//                'shipping_country'      => strval($shippingCountry),
//                'shipping_phone_number' => strval($shippingTelephone),
//
//                'payment_instrument_type'  => 'credit_card',
//                'credit_card_type'        => 'visa',     // TODO - get cc type
//                'total_order_price'        => strval($orderAmount),
//                'currency_code'            => $orderCurrency,
//
//                'event_timestamp' => $eventTime,
//                'ip_address'      => $ipAddress,
//                'user_agent'      => $userAgent
//            ]
//        ]);
//
//        $this->logger->info('3.9.1 -- crs_demo_order');
//        $this->logger->info($result);
    }

}
