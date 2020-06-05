<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Reject extends \Magento\Backend\App\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;
	protected $_dataOnlinedesign;
	protected $_resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
		\Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
        $this->_dataOnlinedesign = $dataOnlinedesign;
    }
	
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::onlinedesign_manage');
    }

    /**
     * Onlinedesign List action
     *
     * @return void
     */
    public function execute()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $_design_file = $this->getRequest()->getParam('_nbdesigner_design_file');  
        $_design_action = $this->getRequest()->getParam('nbdesigner_order_file_approve');  
		$response = array(); $class = "";
		if (is_numeric($order_id) && isset($_design_file) && is_array($_design_file)) {
			$model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Reject');
			$data = array();
			foreach ($_design_file as $pid){
				$this->_dataOnlinedesign->delRecord($order_id, $pid);
				
				$data['oid'] = $order_id;
				$data['pid'] = $pid;
				$data['action'] = $_design_action;
				$model->setData($data);
				$model->save();
				$class .= '.row-'.$pid.'|';
			}
		}
		
		$response['mes'] = 'success';
		$response['action'] = $_design_action;
		$response['change_color'] = $class;
		$result = $this->_resultJsonFactory->create();
        return $result->setData($response);
    }
}
