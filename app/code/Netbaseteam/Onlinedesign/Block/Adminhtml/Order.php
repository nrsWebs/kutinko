<?php
/**
 * Adminhtml onlinedesign list block
 *
 */
namespace Netbaseteam\Onlinedesign\Block\Adminhtml;

class Order extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'Netbaseteam_Onlinedesign';
        $this->_headerText = __('Onlinedesign');
        $this->_addButtonLabel = __('Add New Onlinedesign');
        parent::_construct();
		$this->buttonList->remove('add');
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::save')) {
            $this->buttonList->update('add', 'label', __('Add New Onlinedesign'));
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
