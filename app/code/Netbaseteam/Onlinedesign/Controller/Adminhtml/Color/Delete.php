<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Color;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::onlinedesign_color');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('color_id');
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Color');
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
				/* $this->nbdesigner_delete_color($id); */
                // display success message
                $this->messageManager->addSuccess(__('The data has been deleted.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['page_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a data to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
	
	public function nbdesigner_delete_color($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
        $path = $helper->plugin_path_data().'colors.json';
        $list = $helper->nbdesigner_read_json_setting($path);
        $helper->nbdesigner_delete_json_setting($path, $id);
    }
}
