<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Cache\TypeListInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Netbaseteam\Onlinedesign\Helper\Data
     */
    protected $_dataOnlinedesign;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    protected $_messageManager;
    protected $_responseFactory;
    /**
     * @param Action\Context $context
     * @param \Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param Filesystem $fileSystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(

        Action\Context $context,
        \Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        Filesystem $fileSystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory $onlinedesignFactory,
        PostDataProcessor $dataProcessor,
        TypeListInterface $cacheTypeList
    )
    {
        $this->_resultFactory = $context->getResultFactory();
        $this->_messageManager = $messageManager;
        $this->dataProcessor = $dataProcessor;
        $this->_dataOnlinedesign = $dataOnlinedesign;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_fileSystem = $fileSystem;
        $this->_storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->onlinedesignFactory = $onlinedesignFactory;
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

        $image = $this->getRequest()->getFiles();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Onlinedesign');

            $pid = $data["product_id"];
            $onlinedesign = $this->_dataOnlinedesign->_getOnlineDesignByProduct($pid)->getFirstItem();
            $onlinedesignId = $onlinedesign->getOnlinedesignId();

            if ($onlinedesignId) {
                $model->load($onlinedesignId);
            }

            $data_actual = array();

            if ($data['_nbdesigner_enable'] || $data['_nbdesigner_upload_design_enable']) {
                $data_actual['status'] = $data['_nbdesigner_enable'];
                $data_actual['status_upload_design'] = $data['_nbdesigner_upload_design_enable'];
                $data_actual['use_visual_layout'] = $data['nbd_layout_visual'];
            } else {
                $data_actual['status'] = 0;
                $data_actual['status_upload_design'] = 0;
                $data_actual['use_visual_layout'] = 0;
            }

            $dpi = $data['_nbdesigner_dpi'];
            if (!is_numeric($dpi) || $dpi == "") {
                $dpi = 150;
            }
            $dpi = abs($dpi);
            $data_actual['dpi'] = $dpi;

            $_designer_setting = array();

            /**save design_image**/
            $path = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('nbdesigner/design_image/');
            $designImage = '';
            if (isset($image['design_image'])) {
                $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'nbdesigner/design_image/';
                if (isset($data['design_image']['delete'])) {
                    $designImage = '';
                } else if ($image['design_image']['name'] != '') {
                    /*save image*/
                    try {
                        $uploader = $this->_fileUploaderFactory->create(['fileId' => $image['design_image']]);
                        $fileType = ['jpg', 'jpeg', 'gif', 'png'];
                        $uploader->setAllowedExtensions($fileType);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->save($path);
                        $fileName = $uploader->getUploadedFileName();
                        $designImage = 'nbdesigner/design_image/' . $fileName;
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                        return $resultRedirect;
                    }

                } else {
                    $designImage = (isset($data['old_design_image'])) ? $data['old_design_image'] : '';
                }
            } else {
                $designImage = (isset($data['old_design_image'])) ? $data['old_design_image'] : '';
            }
            /**end**/

            for ($i = 0; $i < count($data['_designer_setting']); $i++) {
                if (isset($data['_designer_setting'][$i]["orientation_name"]) && $data['_designer_setting'][$i]["orientation_name"] != "") {
                    $_designer_setting[] = $data['_designer_setting'][$i];
                }
            }
            /* $setting = serialize($data['_designer_setting']);  */
            $setting = serialize($_designer_setting);

            $data_actual['content_design'] = $setting;

            $option = serialize($data['_nbdesigner_option']);
            $data_actual['nbdesigner_option'] = $option;

            $data_actual['product_id'] = $pid;

            $data_actual['stores'] = $data['store_id_hidden'];

            $data_actual['design_image'] = $designImage;

            $model->addData($data_actual);
            $countItem = count($this->onlinedesignFactory->create()->getCollection());

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['id' => $pid, '_current' => true, 'store' => $data_actual['stores']]);
                return;
            }
            try {
                $this->_eventManager->dispatch('onlinedesign_check_license_free', ['count' => $countItem]);
                $model->save();
                $this->cacheTypeList->cleanType('full_page');
                $this->messageManager->addSuccess(__('The data has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $pid, '_current' => true, 'store' => $data_actual['stores']]);
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
