<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Mapping;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Netbaseteam\Onlinedesign\Model\MappingFactory
     */
    var $mappingFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Netbaseteam\Onlinedesign\Model\MappingFactory $mappingFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Netbaseteam\Onlinedesign\Model\MappingFactory $mappingFactory
    ) {
        parent::__construct($context);
        $this->mappingFactory = $mappingFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('onlinedesign/mapping/additem');
            return;
        }
        try {
            $rowData = $this->mappingFactory->create();
            $rowData->setData($data);
            if (isset($data['entity_id'])) {
                $rowData->setEntityId($data['template_mapping_id']);
            }
            $rowData->save();
            $this->messageManager->addSuccess(__('Item data has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('onlinedesign/mapping/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::save');
    }
}