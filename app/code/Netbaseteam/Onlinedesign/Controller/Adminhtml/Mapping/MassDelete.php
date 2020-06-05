<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Mapping;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Netbaseteam\Onlinedesign\Model\ResourceModel\Mapping\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{

    protected $_filter;
    protected $mappingFactory;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        \Netbaseteam\Onlinedesign\Model\MappingFactory $mappingFactory,
        CollectionFactory $collectionFactory
    )
    {

        $this->_filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->mappingFactory = $mappingFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->_filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $item = $this->mappingFactory->create()->load($item->getTemplateMappingId());
            $item->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 element(s) have been deleted.', $collectionSize));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('onlinedesign/mapping/');
    }
}