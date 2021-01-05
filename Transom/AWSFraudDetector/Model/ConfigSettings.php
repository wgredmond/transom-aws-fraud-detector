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
use Magento\Framework\Encryption\EncryptorInterface;
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
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig,
                                EncryptorInterface $encryptor)
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
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
            'trust_and_safety/transom_aws_fraud_detector/api_enabled',
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
            'trust_and_safety/transom_aws_fraud_detector/api_aws_region',
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
            'trust_and_safety/transom_aws_fraud_detector/detector_id',
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
            'trust_and_safety/transom_aws_fraud_detector/score_name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $scoreName;
    }


    /**
     * Outcome Legit
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOutcomeLegit($storeId = null)
    {
        $outcome = $this->scopeConfig->getValue(
            'trust_and_safety/transom_aws_fraud_detector/outcome_legit',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $outcome;
    }


    /**
     * Outcome Review
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOutcomeReview($storeId = null)
    {
        $outcome = $this->scopeConfig->getValue(
            'trust_and_safety/transom_aws_fraud_detector/outcome_review',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $outcome;
    }


    /**
     * Outcome Cancel
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOutcomeCancel($storeId = null)
    {
        $outcome = $this->scopeConfig->getValue(
            'trust_and_safety/transom_aws_fraud_detector/outcome_cancel',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $outcome;
    }


    /**
     * Update order status feature enabled
     *
     * @return bool
     */
    public function isUpdateOrderStatus()
    {
        $update = $this->scopeConfig->isSetFlag(
            'trust_and_safety/transom_aws_fraud_detector/update_order_status',
            ScopeInterface::SCOPE_WEBSITE
        );
        return $update;
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
            'trust_and_safety/transom_aws_fraud_detector/api_iam_key',
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
            'trust_and_safety/transom_aws_fraud_detector/api_iam_secret',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $iamSecret = $this->encryptor->decrypt($iamSecret);

        return $iamSecret;
    }
}
