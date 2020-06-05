<?php
namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\PageFactory;

class Deletetemplate extends \Magento\Backend\App\Action
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
		$pid = $this->getRequest()->getParam('pid');
		$template_folder = $this->getRequest()->getParam('template_folder');
		
		$collection_extra = $this->_template->create()
							->getCollection() 
							->addFieldToFilter('folder', $template_folder)
							->addFieldToFilter('product_id', $pid);
		
		$current = array();
		
		foreach($collection_extra as $c){
			$current["extra_template_id"] = $c->getId();
			break;
		}
		
		/* set primary for current template */
		$model = $this->_template->create();
		$model->setId($current["extra_template_id"]);
		$model->delete();
		
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
