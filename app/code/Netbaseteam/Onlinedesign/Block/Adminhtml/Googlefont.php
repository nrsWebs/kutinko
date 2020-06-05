<?php
/**
 * Adminhtml googlefont list block
 *
 */
namespace Netbaseteam\Onlinedesign\Block\Adminhtml;

class Googlefont extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_googlefont';
        $this->_blockGroup = 'Netbaseteam_Onlinedesign';
        $this->_headerText = __('Google Font');
        $this->_addButtonLabel = __('Add New Google Font');
        parent::_construct();
		$this->buttonList->remove('add');
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::save')) {
            $this->buttonList->update('add', 'label', __('Add New Google Font'));
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
