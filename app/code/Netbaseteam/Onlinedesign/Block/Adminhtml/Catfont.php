<?php
/**
 * Adminhtml catfont list block
 *
 */
namespace Netbaseteam\Onlinedesign\Block\Adminhtml;

class Catfont extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_catfont';
        $this->_blockGroup = 'Netbaseteam_Onlinedesign';
        $this->_headerText = __('Category Font');
        $this->_addButtonLabel = __('Add New Category Font');
        parent::_construct();
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::onlinedesign_catcustomfont')) {
            $this->buttonList->update('add', 'label', __('Add New Category Font'));
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
