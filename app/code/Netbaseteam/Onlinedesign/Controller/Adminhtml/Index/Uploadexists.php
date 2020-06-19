<?php
namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;

class Uploadexists extends \Magento\Framework\App\Action\Action
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
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
		$this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
	
    /**
     * Default Onlinedesign Index page
     *
     * @return void
     */
    public function execute()
    {
		$helper =  $this->_helper;
        $pid = $this->getRequest()->getParam('id');
        $source_file = $this->getRequest()->getParam('filename');

		$path1 = $helper->getBaseDir();
		if(!is_dir($path1)){
			mkdir($path1, 0755, TRUE);
		}
		
		$year	= strftime("%Y");
		
		$path2 = $path1."/".$year;
		if(!is_dir($path2)){
			mkdir($path2, 0755);
		}
		
		$month 	= strftime("%m");
		$upload_path = $path2."/".$month;
		if(!is_dir($upload_path)){
			mkdir($upload_path, 0755);
		}
		
		$response = array();
		$response['mes'] = '';
		$response['url'] = '';
		
		if($source_file) {
			$filename = basename($source_file);
			$arrContextOptions=array(
			    "ssl"=>array(
			        "verify_peer"=>false,
			        "verify_peer_name"=>false,
			    ),
			);
			$data = base64_encode(file_get_contents($source_file, false, stream_context_create($arrContextOptions)));
			$buffer = base64_decode($data);
			$full_name = $upload_path . '/' . $filename;
			$helper->nbdesigner_save_data_to_image($full_name, $buffer);
			$image_url = $helper->getMediaPath()."/".$year."/".$month."/".$filename;
			$response['mes'] = 'success';
			$response['url'] = $image_url;
		}

		$result = $this->_resultJsonFactory->create();
        return $result->setData($response);
    }
}
