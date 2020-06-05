<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Catfont;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(Action\Context $context, PostDataProcessor $dataProcessor)
    {
        $this->dataProcessor = $dataProcessor;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::onlinedesign_catcustomfont');
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
            $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Catfont');

            $id = $this->getRequest()->getParam('cat_id');
            if ($id) {
                $model->load($id);
            }

            $model->addData($data);

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['cat_id' => $model->getId(), '_current' => true]);
                return;
            }

            try {
                $model->save();
				
				$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
				$path = $helper->plugin_path_data().'font_cat.json';
				$list = $helper->nbdesigner_read_json_setting($path);
				$cat = array(
					'name' => $data['title'],
					'id' => $model->getId()
				);
				$helper->nbdesigner_update_json_setting($path, $cat, $cat['id']);
				
                $this->messageManager->addSuccess(__('The Data has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['cat_id' => $model->getId(), '_current' => true]);
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
            $this->_redirect('*/*/edit', ['cat_id' => $this->getRequest()->getParam('cat_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }
}
