<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Catart;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::catart_manage');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('cat_id');
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Catart');
				
				/* delete all art of categories */
				$modelArt = $this->_objectManager
						->create('Netbaseteam\Onlinedesign\Model\Art')->getCollection()
						->addFieldToFilter("category", $id);
				
				foreach($modelArt as $m) {
					$modelArtDel = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Art');
					$modelArtDel->load($m->getId());
					$modelArtDel->delete();
					$this->nbdesigner_delete_art($m->getId());
				}
				
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
				$this->nbdesigner_delete_art_cat($id);
                // display success message
                $this->messageManager->addSuccess(__('This category and all arts of this category has been deleted.'));
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
	
	public function nbdesigner_delete_art_cat($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
        $path = $helper->plugin_path_data().'art_cat.json';
        $helper->nbdesigner_delete_json_setting($path, $id, false);
        $art_path = $helper->plugin_path_data().'arts.json';
        $helper->nbdesigner_update_json_setting_depend($art_path, $id);
    }
	
	public function nbdesigner_delete_art($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
        $path = $helper->plugin_path_data().'arts.json';
        $list = $helper->nbdesigner_read_json_setting($path);
		$id_found = $helper->indexFound($id, $list, "id");
        $file_art = $helper->plugin_path_data().$list[$id_found]["file"];
        try {
			unlink($file_art);
		} catch (\Exception $e) {}
        $helper->nbdesigner_delete_json_setting($path, $id);
    }
}
