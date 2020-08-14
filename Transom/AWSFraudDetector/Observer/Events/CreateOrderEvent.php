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
use Aws\FraudDetector\FraudDetectorClient;


class CreateOrderEvent implements ObserverInterface
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
        $this->logger->info('>>>> In Transom AWSFraudDetector CreateOrderEvent <<<<<');
        $this->logger->info('0.2');
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

        $result = $client->GetDetectors([
            'detectorId' => 'crs_demo_order', // REQUIRED
            'maxResults' => 10
        ]);
        $this->logger->info('2');
        $this->logger->info($result);

        /*
        {
            "detectorId": "string",
           "maxResults": number,
           "nextToken": "string"
        }
        */
    }
}
