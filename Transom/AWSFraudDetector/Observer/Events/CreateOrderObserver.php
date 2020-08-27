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
use Transom\AWSFraudDetector\Model\ConfigSettings;


class CreateOrderObserver implements ObserverInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigSettings
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

    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                DateTime $eventDate,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->eventDate =  $eventDate;
        $this->remoteAddress = $remoteAddress;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $localLogging = true;

        if ($localLogging) {
            $this->logger->info('##### In Transom AWSFraudDetector ##### CreateOrderObserver');
            $this->logger->info('0.2');
        }

        if (!$this->config->isApiActive()) {
            return $this;
        }

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
            $this->logger->info(' [coo] TODO hook up dont manage credentals in admin option.');
            return $this;
        }

        if ($localLogging) {
            $this->logger->info('1');
            $this->logger->info(' [coo] date type = ' . getType($observer->getData()));
            $this->logger->info(' [coo] event name = ' . $observer->getEvent()->getName());
            foreach ($observer->getData() as $key => $value) {
                $this->logger->info(' [coo] data[' . $key . '] type = ' . getType($value));
                if (getType($value) === 'object') {
                    $this->logger->info(' [coo] object type = ' . get_class($value));
                }
            }
        }

        // get payment
        $payment = $observer->getData('payment');
        if ($localLogging) {
            $this->logger->info(' [coo] payment class = ' . get_class($payment));

            foreach ($payment->getData() as $key => $value) {
                $this->logger->info(' [coo] payment data[' . $key . '] type = ' . getType($value));
                if (getType($value) === 'object') {
                    $this->logger->info(' [coo] payment object type = ' . get_class($value));
                }
            }

            $this->logger->info(' [coo] processPayment; entity id = ' . $payment->getEntityId());
            $this->logger->info(' [coo] processPayment; amount authorized = ' . $payment->getAmountAuthorized());
            $this->logger->info(' [coo] processPayment; method = ' . $payment->getMehtod());
            $this->logger->info(' [coo] processPayment; cc type = ' . $payment->getCcType());
            $this->logger->info(' [coo] processPayment; cc cid statusa = ' . $payment->getCcCidStatus());
            $this->logger->info(' [coo] processPayment; cc status = ' . $payment->getCcStatus());
            $this->logger->info(' [coo] processPayment; cc trans id = ' . $payment->getCcTransId());
            $this->logger->info(' [coo] processPayment; cc last 4 = ' . $payment->getCcLast4());
            $this->logger->info(' [coo] processPayment; parwnt id = ' . $payment->getParentId());
            $this->logger->info(' [coo] processPayment; transaction id = ' . $payment->getTransactionId());

            foreach ($payment->getAdditionalInformation() as $key => $value) {
                $this->logger->info(' [coo] payment additional info[' . $key . '] type = ' . getType($value));
                if (getType($value) === 'object') {
                    $this->logger->info(' [coo] payment additional info object type = ' . get_class($value));
                }
            }
        }

        // get order
        $order = $observer->getEvent()->getOrder();

        if ($localLogging) {
            $this->logger->info('3.1');
        }

        //  If order data is empty then doesn't need to process
        if (empty($order)) {
            $this->logger->info('There is an error in CreateOrderObserver');
            return $this;
        }

        if ($localLogging) {
            $this->logger->info('3.2');
        }

        // Order main info
        $orderId           = $order->getIncrementId();
        $orderAmount       = $order->getGrandTotal();
        $orderCurrency     = $order->getOrderCurrencyCode();
        $this->logger->info('3.3 -- orderId = ' . $orderId);
        if ($localLogging) {
            $this->logger->info('3.3 -- orderAmount = ' . $orderAmount);
            $this->logger->info('3.3 -- orderCurrency = ' . $orderCurrency);
            $this->logger->info('3.3 -- $orderId = ' . $orderId);
        }

        // Customer main info
        $customerId        = $order->getCustomerId();
        $customerEmail     = $order->getCustomerEmail();
        //$session            = $this->customerSession->getMyValue();
        $userAgent     = $_SERVER ['HTTP_USER_AGENT'];

        if ($localLogging) {
            $this->logger->info('3.4 -- customerId = ' . $customerId);
            $this->logger->info('3.4 -- customerEmail = ' . $customerEmail);
        }

        // Billing Address details
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

        if ($localLogging) {
            $this->logger->info('3.5 -- billingName = ' . $billingName);
            $this->logger->info('3.5 -- billingZipCode = ' . $billingZipCode);
        }

        // Shipping Address details
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

        if ($localLogging) {
            $this->logger->info('3.6 -- shippingName = ' . $shippingName);
            $this->logger->info('3.6 -- shippingZipCode = ' . $shippingZipCode);
        }

        $eventTime = $this->eventDate->format('Y-m-d\TH:i:s.').gettimeofday()['usec'] . 'Z';
        $eventId = $orderId . '-' . $this->eventDate->format('Y-m-d_H-i-s-').gettimeofday()['usec'];

        if ($localLogging) {
            $this->logger->info('3.7 -- eventTime = ' . $eventTime);
            $this->logger->info('3.7 -- eventId = ' . $eventId);
        }

        $ipAddress = $this->remoteAddress->getRemoteAddress();

        if ($localLogging) {
            $this->logger->info('3.8 -- ipAddress = ' . $ipAddress);
            $this->logger->info('3.8 -- userAgent = ' . $userAgent);
        }

        if ($localLogging) {
            $this->logger->info('3.9.1.0.1 -- crs_demo_order');
        }

        $result = $client->GetEventPrediction([
            'detectorId' => 'create_order_detector',
            'eventId' => 'crs-' . $eventId,
            'eventTypeName' => "create_order",
            'eventTimestamp'    => $eventTime,
            'entities' => [[
                'entityType'    => 'customer',
                'entityId'      => strval($customerId)
            ]],
            'eventVariables' => [
                'order_id'          => strval($orderId),
                'user_id'           => strval($customerId),
                'email_address'     => $customerEmail,
                'user_name'         => $billingName,

                'billing_name'         => strval($billingName),
                'billing_address_1'    => strval($billingAddress1),
                'billing_city'         => strval($billingCity),
                'billing_state'        => strval($billingRegion),
                'billing_zip'          => strval($billingZipCode),
                'billing_country'      => strval($billingCountry),
                'billing_phone_number' => strval($billingTelephone),

                'shipping_name'         => strval($shippingName),
                'shipping_address_1'    => strval($shippingAddress1),
                'shipping_city'         => strval($shippingCity),
                'shipping_state'        => strval($shippingRegion),
                'shipping_zip'          => strval($shippingZipCode),
                'shipping_country'      => strval($shippingCountry),
                'shipping_phone_number' => strval($shippingTelephone),

                'payment_instrument_type'  => 'credit_card',
                'credit_card_type'        => 'visa',     // TODO - get cc type
                'total_order_price'        => strval($orderAmount),
                'currency_code'            => $orderCurrency,

                'event_timestamp' => $eventTime,
                'ip_address'      => $ipAddress,
                'user_agent'      => $userAgent
            ]
        ]);

        if ($localLogging) {
            $this->logger->info('3.9.1 -- crs_demo_order');
            $this->logger->info($result);
        }
    }

//    private function _processPayment(\Magento\Sales\Model\Order\Payment\Interceptor $payment) {
//        $this->logger->info(' [coo] processPayment; amount authorized = ' . $payment->getAmountAuthorized());
//
//        $payment->getMethod();
//        $payment->getAmountAuthorized();
//        $payment->getCcType();
//        $payment->getEntityId();
//       s $payment->getCcCidStatus();
//        $payment->getCcStatus();
//        $payment->getCcTransId();
//        $payment->getCcLast4();
//        $payment->getParentId();
//        $payment->getTransactionId();
//
//        foreach ($payment->getData() as $key => $value) {
//            $this->logger->info(' [coo] payment data[' . $key . '] type = ' . getType($value));
//            if (getType($value) === 'object') {
//                $this->logger->info(' [coo] payment object type = ' . get_class($value));
//            }
//        }
//
//    }
}
