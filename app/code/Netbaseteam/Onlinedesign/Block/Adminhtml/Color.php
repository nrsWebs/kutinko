<?php
/**
 * Adminhtml color list block
 *
 */
namespace Netbaseteam\Onlinedesign\Block\Adminhtml;

class Color extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_color';
        $this->_blockGroup = 'Netbaseteam_Onlinedesign';
        $this->_headerText = __('Color');
        $this->_addButtonLabel = __('Add New Color');
        parent::_construct();
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::onlinedesign_color')) {
            $this->buttonList->update('add', 'label', __('Add New Color'));
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
