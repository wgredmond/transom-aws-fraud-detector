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

class TransactionEvent implements ObserverInterface
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
        $this->logger->info('##### In Transom AWSFraudDetector ##### TransactionEvent');

        $this->logger->info(' [te] date type = ' . getType($observer->getData()));
        $this->logger->info(' [te] event name = ' . $observer->getEvent()->getName());
        foreach ($observer->getData() as $key => $value) {
            $this->logger->info(' [te] data[' . $key . '] type = ' . getType($value));
            if (getType($value) === 'object') {
                $this->logger->info(' [te] object type = ' . get_class($value));
            }
        }

        // get payment and order
        $payment = $observer->getData('payment');
        $order = $payment->getOrder();

        if ($payment !== null) {
            $this->logger->info(' [te] payment id = ' . $payment->getEntityId());
            $this->logger->info(' [te] payment amount authorized = ' . $payment->getAmountAuthorized());
            $this->logger->info(' [te] payment cc type = ' . $payment->getCcType());
            $this->logger->info(' [te] payment cc last 4 = ' . $payment->getCcLast4());
        }

        if ($order !== null) {
            $this->logger->info(' [te] order id = ' . $order->getEntityId());
        }

    }
}
