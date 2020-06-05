<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Googlefont;

use Magento\Backend\App\Action;

class Update extends \Magento\Backend\App\Action
{
    protected $_helperData;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Netbaseteam\Onlinedesign\Helper\Data $helperData
    )
    {
        $this->_helperData = $helperData;
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
        require_once $this->_helperData->getLibOnlineDesign() . '/Onlinedesign/includes/class.nbdesigner.php';
        require_once $this->_helperData->getLibOnlineDesign() . '/Onlinedesign/includes/class.helper.php';
        require_once $this->_helperData->getLibOnlineDesign() . '/Onlinedesign/includes/class-util.php';

        $nbdesigner = new \Nbdesigner_Plugin();
        $nbdesigner->nbdesigner_add_google_font();

        return;
    }
}
