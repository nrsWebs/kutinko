<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Color;

use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * File Uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        PostDataProcessor $dataProcessor,
        TypeListInterface $cacheTypeList
    )
    {
        $this->dataProcessor = $dataProcessor;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::onlinedesign_color');
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
            $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Color');

            $id = $this->getRequest()->getParam('color_id');
            if ($id) {
                $model->load($id);
            }

            $model->addData($data);

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['color_id' => $model->getId(), '_current' => true]);
                return;
            }

            try {
                $model->save();

                $this->cacheTypeList->cleanType('full_page');

                $this->messageManager->addSuccess(__('The Data has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['color_id' => $model->getId(), '_current' => true]);
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
            $this->_redirect('*/*/edit', ['color_id' => $this->getRequest()->getParam('color_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }
}
