<?php
/**
 * Adminhtml font list block
 *
 */
namespace Netbaseteam\Onlinedesign\Block\Adminhtml;

class Font extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_font';
        $this->_blockGroup = 'Netbaseteam_Onlinedesign';
        $this->_headerText = __('Font');
        $this->_addButtonLabel = __('Add New Custom Font');
        parent::_construct();
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::onlinedesign_customfont')) {
            $this->buttonList->update('add', 'label', __('Add New Custom Font'));
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
