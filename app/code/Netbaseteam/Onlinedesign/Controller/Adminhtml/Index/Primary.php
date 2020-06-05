<?php
namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;

class Primary extends \Magento\Backend\App\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_helperOnlinedesign;
    protected $_template;
	protected $_resultJsonFactory;
	
	/**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
		\Netbaseteam\Onlinedesign\Model\TemplateFactory $template,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
        $this->_helperOnlinedesign = $helper;
        $this->_template = $template;
        parent::__construct($context);
    }
	
    /**
     * Default Onlinedesign Index page
     *
     * @return void
     */
    public function execute()
    {
		$helper =  $this->_helperOnlinedesign;
        $pid = $this->getRequest()->getParam('id');
	
		$primary_str = "primary";
		$pid = $this->getRequest()->getParam('pid');
		
		$template_folder = $this->getRequest()->getParam('template_folder');
		
		$admin_path = $helper->plugin_path_data().'admindesign/'.$pid;
		
		$collection_primary = $this->_template->create()
							->getCollection() 
							->addFieldToFilter('folder', $primary_str)
							->addFieldToFilter('product_id', $pid);
		$collection_extra = $this->_template->create()
							->getCollection() 
							->addFieldToFilter('folder', $template_folder)
							->addFieldToFilter('product_id', $pid);
		
		$primary = array();
		$current = array();
		
		foreach($collection_primary as $c){
			$primary["primary_template_id"] = $c->getId();
			$primary["primary_user_id"] = $c->getUserId();
			break;
		}
		
		foreach($collection_extra as $c){
			$current["extra_template_id"] = $c->getId();
			$current["extra_user_id"] = $c->getUserId();
			$current["extra_folder"] = $c->getFolder();
			break;
		}

		
		/* set primary for current template */
		$model = $this->_template->create();
		$model->setFolder($primary_str)->setUserId($primary["primary_user_id"])->setPriority(1)->setId($current["extra_template_id"]);
		$model->save();
		/* set extra for primary template */
		$model = $this->_template->create();
		$model->setFolder($current["extra_folder"])->setUserId($current["extra_user_id"])->setPriority(0)->setId($primary["primary_template_id"]);
		$model->save();
		
		/* rename primary to primary_ */
		$rename = rename($admin_path."/".$primary_str, $admin_path."/".$primary_str."_");
		
		/* rename template_folder to primary */
		$rename = rename($admin_path."/".$template_folder, $admin_path."/".$primary_str);
		/* rename primary_ to template_folder */
		$rename = rename($admin_path."/".$primary_str."_", $admin_path."/".$template_folder);
		
		$response = array();
		$response['mes'] = "successfully";
		$result = $this->_resultJsonFactory->create();
        return $result->setData($response);
    }
	
	/**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
