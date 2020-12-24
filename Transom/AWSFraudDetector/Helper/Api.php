<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\AWSFraudDetector\Helper;

use Aws\FraudDetector\FraudDetectorClient;
use DateTime;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Psr\Log\LoggerInterface;
use Transom\AWSFraudDetector\Model\ConfigSettings;
use Transom\AWSFraudDetector\Model\OrderManager;
use Transom\AWSFraudDetector\Model\OrderScoreFactory;
use Transom\AWSFraudDetector\Model\ResourceModel\OrderScore;


class Api extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var \Transom\AWSFraudDetector\Model\OrderScoreFactory
     */
    protected $orderScoreFactory;

    /**
     * @var \Transom\AWSFraudDetector\Model\ResourceModel\OrderScore
     */
    protected $orderScoreResource;

    /**
     * @var \Transom\AWSFraudDetector\Model\OrderManager
     */
    protected $orderManager;

    /**
     * @var DateTime
     */
    protected $eventDate;


    /**
     * Api constructor.
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     * @param OrderScoreFactory $orderScoreFactory
     * @param OrderScore $orderScoreResource
     * @param OrderManager $orderManager
     * @param DateTime $eventDate
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                OrderScoreFactory $orderScoreFactory,
                                OrderScore $orderScoreResource,
                                OrderManager $orderManager,
                                DateTime $eventDate,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->orderScoreFactory = $orderScoreFactory;
        $this->orderScoreResource = $orderScoreResource;
        $this->orderManager = $orderManager;
        $this->eventDate =  $eventDate;
        $this->remoteAddress = $remoteAddress;
    }


    /**
     * send transaction event data to aws
     * @param $order
     * @return \Transom\AWSFraudDetector\Helper\Api
     */
    public function sendTransaction(\Magento\Sales\Model\Order\Interceptor $order, \Magento\Sales\Model\Order\Payment\Interceptor $payment)
    {

        // get cretentials, create AWS API client instance
        $client = new FraudDetectorClient([
            'version' => 'latest',
            'region' => $this->config->getApiAwsRegion(),
            'credentials' => [
                'key' => $this->config->getApiIamKey(),
                'secret' => $this->config->getApiIamSecret()
            ]
        ]);

        //  if order data is empty then doesn't need to process
        if (empty($order)) {
            $this->logger->info('There is an error in sendTransation()');
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

        // Notice: Undefined property: Transom\AWSFraudDetector\Helper\Api::$eventDate in /var/www/vhosts/dev.m2.local.com/app/code/Transom/AWSFraudDetector/Helper/Api.php on line 143

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
                    'billing_phone_number' => strval($billingTelephone),

                    'shipping_name' => strval($shippingName),
                    'shipping_address_1' => strval($shippingAddress1),
                    'shipping_city' => strval($shippingCity),
                    'shipping_state' => strval($shippingRegion),
                    'shipping_zip' => strval($shippingZipCode),
                    'shipping_country' => strval($shippingCountry),
                    'shipping_phone_number' => strval($shippingTelephone),

                    'payment_instrument_type' => 'credit_card',
                    'credit_card_type' => 'n/a',
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
            $this->orderManager->updateOrderStatus($insightScore, $outcome, $scoreName, $order);
            $this->logger->info(' ### In sendTransaction(); insightScore = ' . $insightScore);
            $this->logger->info(' ### In sendTransaction(); outcome = ' . $outcome);

            // save score and outcome to order extension attributes
            $order->getExtensionAttributes()->setAwsInsightScore($insightScore);
            $order->getExtensionAttributes()->setAwsOutcome($outcome);

        } catch (\Throwable $exception) {
            $this->logger->critical('Exception in OrderObserver -- ' . $exception->getMessage());
            // let order complete
        }
    }


    /**
     * @param $orderId
     * @param $insightScore
     * @param $outcome
     */
    public function saveOrderScore($orderId, $insightScore, $outcome) {
        $this->logger->info(' ### In saveOrderScore(); orderId = ' . $orderId);
        $this->logger->info(' ### In saveOrderScore(); insightScore = ' . $insightScore);
        $this->logger->info(' ### In saveOrderScore(); outcome = ' . $outcome);

        try {
            $orderScoreInterface = $this->orderScoreFactory->create();
            $orderScoreInterface->setData('order_id', $orderId);
            $orderScoreInterface->setData('insight_score', $insightScore);
            $orderScoreInterface->setData('outcome', $outcome);
            $this->orderScoreResource->save($orderScoreInterface);
        } catch (\Exception $e) {
            $this->logger->info('Exception saving AWS Fraud score: ' . $e->getMessage());
        }
    }
}
