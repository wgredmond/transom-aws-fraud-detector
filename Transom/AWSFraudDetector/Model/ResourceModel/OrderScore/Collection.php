<?php
namespace Transom\AWSFraudDetector\Model\ResourceModel\OrderScore;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'aws_fraud_order_score_id';
    protected $_eventPrefix = 'transom_awsfrauddetector_score_collection';
    protected $_eventObject = 'score_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Transom\AWSFraudDetector\Model\OrderScore', 'Transom\AWSFraudDetector\Model\ResourceModel\OrderScore');
    }

}
