<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Font;

use Magento\Backend\App\Action;

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
		PostDataProcessor $dataProcessor
		)
    {
        $this->dataProcessor = $dataProcessor;
		$this->_fileUploaderFactory = $fileUploaderFactory;
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
			$uploader = $this->_objectManager->create(
				'Magento\MediaStorage\Model\File\Uploader',
				['fileId' => 'filename']
			);
			$file = $uploader->validateFile();
			$fName = explode(".", $file['name']);
			if($data["title"] == "") {
				$data["title"] = trim($fName[0]);
			}
			
            $data = $this->dataProcessor->filter($data);
            $model = $this->_objectManager->create('Netbaseteam\Onlinedesign\Model\Font');

            $id = $this->getRequest()->getParam('font_id');
            if ($id) {
                $model->load($id);
            }
			
			if(!$id){
				if(!isset($file['name']) || $file['name'] == '' || $file['size'] <= 0) {
					$this->messageManager->addError( __('Please attach font.'));
					$this->_redirect('*/*/new');
					return;
				} else {
					$data["title"] = $file['name'];
				}
			}
            
            // save image data and remove from data array
            if (isset($data['filename'])) {
                $imageData = $data['filename'];
                unset($data['filename']);
            } else {
                $imageData = array();
            }
			
			$alias = 'nbfont' . substr(md5(rand(0, 999999)), 0, 10);
			$data['alias'] = $alias;

            $model->addData($data);

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['font_id' => $model->getId(), '_current' => true]);
                return;
            }

            /* try { */
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
				$font_id = $model->getId();	
				$cats = ["0"];
				$current_font_cat_id = 0;
				$update = false;
				$list = $helper->nbdesigner_read_json_setting($helper->plugin_path_data(). 'fonts.json');	
				$cat = $helper->nbdesigner_read_json_setting($helper->plugin_path_data().'font_cat.json');
				/* $data_font_google = $helper->nbdesigner_read_json_setting($helper->plugin_path_data(). "/" . 'googlefonts.json');
				$list_all_google_font = $helper->nbdesigner_get_list_google_font(); */
				$current_cat = filter_input(INPUT_GET, "cat_id", FILTER_VALIDATE_INT);
				if (is_array($cat))
					$current_font_cat_id = sizeof($cat);
				
				if ($id) {
					$font_index_found = $helper->indexFound($font_id, $list, "id");
					$update = true;
					if (isset($list[$font_index_found])) {
						$font_data = $list[$font_index_found];
						$cats = $font_data["cat"];
					}
				}
			  
				$font = array();
				$font['name'] = $data["title"];
				$font['alias'] = $alias;
				$font['id'] = $font_id;
				$font['cat'] = $cats;
				
				$arr_cat = array();
				$arr_cat[] = "0"; 
				
				if (isset($data["category"]))
					$arr_cat[] = $data["category"];

				$font['cat'] = $arr_cat;
				
				if(isset($file['name']) && $file['name'] != '' && $file['size']) {
					
					$font['type'] = $helper->nbdesigner_get_extension($file['name']);              
					$path1 = $helper->getBaseDir();
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
					$uploader->setAllowedExtensions(['woff', 'ttf']);
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(false);
					$uploader->setAllowCreateFolders(true);
					
					$uploader->save($upload_path);

					$font['file'] = $helper->plugin_path_data().$year."/".$month."/".$file['name'];
					$font['url'] = $helper->getMediaPath()."/".$year."/".$month."/".$file['name'];
	
				} else {
					$font_index_found = $helper->indexFound($font_id, $list, "id");
					if (isset($list[$font_index_found])) {
						$font_data = $list[$font_index_found];	
						$font['file'] = $font_data["file"];
						$font['url'] = $font_data["url"];
					}
				}
				
				if ($update) {
					$helper->nbdesigner_update_list_fonts($font, "update", $font_id);
				} else {
					$helper->nbdesigner_update_list_fonts($font, "add", $font_id);
				}
				
				/************************************************/
				
                $this->messageManager->addSuccess(__('The Data has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['font_id' => $model->getId(), '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            /* } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            } */

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', ['font_id' => $this->getRequest()->getParam('font_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }
}
