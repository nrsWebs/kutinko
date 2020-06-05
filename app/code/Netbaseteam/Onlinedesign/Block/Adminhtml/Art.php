<?php
/**
 * Adminhtml art list block
 *
 */
namespace Netbaseteam\Onlinedesign\Block\Adminhtml;

class Art extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_art';
        $this->_blockGroup = 'Netbaseteam_Onlinedesign';
        $this->_headerText = __('Art');
        $this->_addButtonLabel = __('Add New Art');
        parent::_construct();
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::onlinedesign_art')) {
            $this->buttonList->update('add', 'label', __('Add New Art'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
