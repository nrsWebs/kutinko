<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Art;

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
            $id = $this->getRequest()->getParam('art_id');

            try {
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'filename']
                );
                $file = $uploader->validateFile();
            } catch (\Exception $e) {
                if(!$id) {
                    $this->messageManager->addError(__("Please choose an art image"));
                    $this->_redirect('*/*/new');
                    return;
                }
                //Logic for when $_FILES['field_name'] is not set or file fails to save
            }


            if(isset($file)) {
                $fName = explode(".", $file["name"]);
                if($data["title"] == "") {
                    $data["title"] = trim($fName[0]);
                }
            }

            $data = $this->dataProcessor->filter($data);
            $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Art');

            if ($id) {
                $model->load($id);
            }

            // save image data and remove from data array
            if (isset($data['filename'])) {
                $imageData = $data['filename'];
            } else {
                $imageData = array();
            }

            unset($data['filename']);

            $model->addData($data);

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['art_id' => $model->getId(), '_current' => true]);
                return;
            }

            try {
                $helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');

                if (isset($imageData['delete']) && $model->getFilename()) {
                    $helper->removeImage($model->getFilename());
                    $model->setFilename(null);
                }

                /* $imageFile = $helper->uploadImage('filename');
                if ($imageFile) {
                    $model->setFilename($imageFile);
                } */

                $model->save();

                /************************************************/
                $current_art_cat_id = 0;
                $art_id = 0;
                $update = false;
                $cats = ["0"];
                $list = $helper->nbdesigner_read_json_setting($helper->plugin_path_data().'arts.json');
                $cat = $helper->nbdesigner_read_json_setting($helper->plugin_path_data().'art_cat.json');

                if (is_array($cat))
                    $current_art_cat_id = sizeof($cat);
                if ($id) {
                    $art_id = $model->getId();

                    $art_index_found = $helper->indexFound($art_id, $list, "id");

                    $update = true;
                    if (isset($list[$art_index_found])) {
                        $art_data = $list[$art_index_found];
                        $cats = $art_data["cat"];
                    }
                }

                $art = array();
                $art['name'] = $data["title"];
                $art['id'] = $model->getId();
                $art['cat'] = $cats;

                $arr_cat = array();
                $arr_cat[] = "0";

                if (isset($data["category"]))
                    $arr_cat[] = $data["category"];

                $art['cat'] = $arr_cat;

                if(isset($file["name"]) && $file["name"] != '' && $file['size']) {
                    $uploaded_file_name = basename($file['name']);
                    $path1 = $helper->getBaseDir().'/cliparts/';

                    if(!is_dir($path1)){
                        mkdir($path1, 0777, TRUE);
                    }

                    $year	= strftime("%Y");

                    $path2 = $path1."/".$year;
                    if(!is_dir($path2)){
                        mkdir($path2, 0777);
                    }

                    $month 	= strftime("%m");

                    $upload_path = $path2."/".$month;
                    if(!is_dir($upload_path)){
                        mkdir($upload_path, 0777);
                    }

                    /* Starting upload */
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'filename']);
                    $uploader->setAllowedExtensions(['svg', 'png', 'jpg', 'jpeg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $uploader->setAllowCreateFolders(true);

                    $uploader->save($upload_path);

                    $fileName = $uploader->getUploadedFileName();

                    $art['file'] = "/".$year."/".$month."/".$fileName;
                    $art['url'] = "/".$year."/".$month."/".$fileName;
                } else {
                    $art_id = $model->getId();
                    $art_index_found = $helper->indexFound($art_id, $list, "id");
                    if (isset($list[$art_index_found])) {
                        $art_data = $list[$art_index_found];
                        $art['file'] = $art_data["file"];
                        $art['url'] = $art_data["url"];
                    }
                }

                if ($update) {
                    $helper->nbdesigner_update_list_arts($art, $art_id, "update");
                } else {
                    $art_id = $model->getId();
                    $helper->nbdesigner_update_list_arts($art, $art_id, "add");
                }

                $this->cacheTypeList->cleanType('full_page');

                /************************************************/

                $this->messageManager->addSuccess(__('The Data has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['art_id' => $model->getId(), '_current' => true]);
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
            $this->_redirect('*/*/edit', ['art_id' => $this->getRequest()->getParam('art_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }
}
