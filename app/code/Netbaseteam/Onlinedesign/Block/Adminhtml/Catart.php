<?php
/**
 * Adminhtml catart list block
 *
 */
namespace Netbaseteam\Onlinedesign\Block\Adminhtml;

class Catart extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_catart';
        $this->_blockGroup = 'Netbaseteam_Onlinedesign';
        $this->_headerText = __('Category Art');
        $this->_addButtonLabel = __('Add New Category Art');
        parent::_construct();
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::catart_manage')) {
            $this->buttonList->update('add', 'label', __('Add New Category Art'));
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
