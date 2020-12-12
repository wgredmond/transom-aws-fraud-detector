<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\AWSFraudDetector\Model;


class OrderScore extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'aws_order_score';

    protected $_cacheTag = 'aws_order_score';

    protected $_eventPrefix = 'aws_order_score';

    protected function _construct()
    {
        $this->_init('Transom\AWSFraudDetector\Model\ResourceModel\OrderScore');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

}
