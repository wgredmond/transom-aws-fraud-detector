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


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 *
 * Class for retrieving configuration settings.
 */
class ConfigSettings
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Is AWS Fraud Detector API feature enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isApiActive($storeId = null)
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'fraud_protection/transom_aws_fraud_detector/api_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $enabled;
    }


    /**
     * Is AWS Fraud Detector API region
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiAwsRegion($storeId = null)
    {
        $apiKey = $this->scopeConfig->getValue(
            'fraud_protection/transom_aws_fraud_detector/api_aws_region',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $apiKey;
    }


    /**
     * Detector Id
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDetectorId($storeId = null)
    {
        $detectorId = $this->scopeConfig->getValue(
            'fraud_protection/transom_aws_fraud_detector/detector_id',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $detectorId;
    }


    /**
     * Score name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getScoreName($storeId = null)
    {
        $scoreName = $this->scopeConfig->getValue(
            'fraud_protection/transom_aws_fraud_detector/score_name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $scoreName;
    }


    /**
     * Is AWS Fraud Detector - Manager API access keys in admin?
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isApiAccessKeysInAdmin($storeId = null)
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'fraud_protection/transom_aws_fraud_detector/api_access_keys_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $enabled;
    }


    /**
     * AWS IAM Key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiIamKey($storeId = null)
    {
        $iamKey = $this->scopeConfig->getValue(
            'fraud_protection/transom_aws_fraud_detector/api_iam_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $iamKey;
    }

    /**
     * AWS IAM Secret
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiIamSecret($storeId = null)
    {
        $iamSecret = $this->scopeConfig->getValue(
            'fraud_protection/transom_aws_fraud_detector/api_iam_secret',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $iamSecret;
    }
}
