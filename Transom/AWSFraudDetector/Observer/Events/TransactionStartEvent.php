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

class TransactionStartEvent implements ObserverInterface
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
        $this->logger->info('##### In Transom AWSFraudDetector ##### TransactionStartEvent');
        $this->logger->info(' [tse] date type = ' . getType($observer->getData()));
        $this->logger->info(' [tse] event name = ' . $observer->getEvent()->getName());
        foreach ($observer->getData() as $key => $value) {
            $this->logger->info(' [tse] data[' . $key . '] type = ' . getType($value));
            if (getType($value) === 'object') {
                $this->logger->info(' [tse] object type = ' . get_class($value));
            }
        }


    }
}
