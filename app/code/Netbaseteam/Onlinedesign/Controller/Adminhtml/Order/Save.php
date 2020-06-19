<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;
	protected $_dataOnlinedesign;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
		Action\Context $context, 
		\Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
		PostDataProcessor $dataProcessor
	)
    {
        $this->dataProcessor = $dataProcessor;
        $this->_dataOnlinedesign = $dataOnlinedesign;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::save');
    }

    /**
     * Save action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Onlinedesign');
			
            $pid = $data["product_id"];  		
			$onlinedesignIds = $this->_dataOnlinedesign->_getOnlineDesignByProduct($pid);

			if(sizeof($onlinedesignIds)) {
				foreach ($onlinedesignIds as $od) {
					$od->delete();
				}
			}
				
	  		$data_actual = array();
			
			$data_actual['status'] = $data['_nbdesigner_enable']; 
			$dpi = $data['_nbdesigner_dpi']; 
			if(!is_numeric($dpi) || $dpi == "") $dpi = 150;
			$dpi = abs($dpi);
			$data_actual['dpi'] = $dpi;

			$_designer_setting = array();
			
			for($i=0; $i<count($data['_designer_setting']); $i++){
				if(isset($data['_designer_setting'][$i]["orientation_name"]) && $data['_designer_setting'][$i]["orientation_name"] != "") {
					$_designer_setting[] = $data['_designer_setting'][$i];
				}		
			}
			
			/* $setting = serialize($data['_designer_setting']);  */
			$setting = serialize($_designer_setting); 
			
			$data_actual['content_design'] = $setting;	
			
			$option = serialize($data['_nbdesigner_option']); 
			$data_actual['nbdesigner_option'] = $option;	
			
			$data_actual['product_id'] = $pid;	

            $model->addData($data_actual);

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['id' => $pid, '_current' => true]);
                return;
            }

            try {

                $model->save();
                $this->messageManager->addSuccess(__('The Data has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $pid, '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', ['id' => $pid]);
            return;
        }
        $this->_redirect('*/*/');
    }
}
