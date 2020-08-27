<?php

namespace Transom\AWSFraudDetector\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class AmazonVariables
 */
class AWSFraudDetectorVariables extends AbstractFieldArray
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $context;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Psr\Log\LoggerInterface $logger,
        $data = [])
    {
        $this->logger = $logger;
        //$this->logger(' ***** ***** ***** AmazonVariables In __construct()');
        parent::__construct($context, $data);
    }

    protected $_template = 'Transom_AWSFraudDetector::array.phtml';


    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
//        $this->logger(' ***** ***** ***** AmazonVariables In _prepareToRender()');
//        foreach ($this->_columns as $key => $value) {
//            $this->logger(' ### before ### AmazonVariables _prepareToRender(); key = ' . $key . '; value = ' . $value);
//        }

        $this->addColumn('varname', ['label' => __('Variable name'), 'class' => 'required-entry']);
        $this->addColumn('datatype', ['label' => __('Data type')]);
        $this->addColumn('vartype', ['label' => __('Variable type')]);
        $this->addColumn('source', ['label' => __('Source')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Variable');

//        foreach ($this->_columns as $key => $value) {
//            $this->logger(' ### after ### AmazonVariables _prepareToRender(); key = ' . $key . '; value = ' . $value);
//        }

    }
}
