<?php
namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;

class Uploadmyimages extends \Magento\Framework\App\Action\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_helper;
	protected $_resultJsonFactory;
	
	/**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        parent::__construct($context);
    }
	
    /**
     * Default Onlinedesign Index page
     *
     * @return void
     */
    public function execute()
    {
		$response = array();
		$response['mes'] = '';
		
		$helper =  $this->_helper;
        $pid = $this->getRequest()->getParam('id');
		$product_id_path = $helper->getBaseDir().'/productimages/'.$pid;
		if(!is_dir($product_id_path)){
			mkdir($product_id_path, 0777, true);
		}
		
		$uploader = $this->_objectManager->create(
		  'Magento\MediaStorage\Model\File\Uploader',
		  ['fileId' => 'file']
		);
		$file = $uploader->validateFile();
		
		$filetype = array('jpeg','jpg','png','PNG','JPEG','JPG','SVG', 'svg');

		$name =time().$file["name"];
		$path = $product_id_path."/".$name;
		$file_ext =  pathinfo($name, PATHINFO_EXTENSION);
		if(in_array(strtolower($file_ext), $filetype)){
			if($file["size"] < 1000000) {
				@move_uploaded_file($file['tmp_name'],$path);
				$response['mes'] = $name;
			} else {
				$response['mes'] = "FILE_SIZE_ERROR";
			}
		} else {
			$response['mes'] = "FILE_TYPE_ERROR";
		}
		$result = $this->_resultJsonFactory->create();
        return $result->setData($response);
    }
}
