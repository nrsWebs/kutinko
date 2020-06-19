<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Templategrid extends \Magento\Backend\App\Action
{

    /**
     * Customer grid action
     *
     * @return void
     */
    public function execute()
    {
		$this->_view->loadLayout();
        $this->getResponse()->setBody(
              $this->_view->getLayout()->createBlock('Netbaseteam\Onlinedesign\Block\Adminhtml\Onlinedesign\Edit\Tab\Templatelist')->toHtml()
        );
    }
}
