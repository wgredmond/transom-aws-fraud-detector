<?php

namespace Transom\AWSFraudDetector\Model\ResourceModel;

class OrderScore extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('aws_fraud_order_score', 'aws_fraud_order_score_id');
    }

}
