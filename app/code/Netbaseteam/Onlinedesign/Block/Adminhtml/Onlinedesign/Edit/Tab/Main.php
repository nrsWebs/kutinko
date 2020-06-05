<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Onlinedesign\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_productloader;
    protected $_onlineCollectionFactory;
    protected $_resourceModel;
    protected $_helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlineCollectionFactory,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign $resourceModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_systemStore = $systemStore;
        $this->_productloader = $_productloader;
        $this->_onlineCollectionFactory = $onlineCollectionFactory;
        $this->_resourceModel = $resourceModel;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Netbaseteam\Onlinedesign\Model\Onlinedesign */
        $model = $this->_coreRegistry->registry('onlinedesign');

        $params = $this->getRequest()->getParams();

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('onlinedesign_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product Information')]);

        if ($model->getId()) {
            $fieldset->addField('product_id', 'hidden', ['name' => 'onlinedesign_id']);
        }

        $fieldset->addField(
            'store_id_hidden',
            'hidden',
            [
                'name' => 'store_id_hidden'
            ]
        );

        $id = $this->getRequest()->getParam('id');
        $product = $this->_productloader->create()->load($id);

        $str_p = '<strong><a href="' . $this->getUrl('catalog/product/edit', array('id' => $id)) . '" onclick="this.target=\'blank\'">'. $product->getName() . '</a></strong>';

        $fieldset->addField('pname', 'note', array(
            'label'     => __('Product Name'),
            'name'      => 'note',
            'text'      => $str_p,
        ));

        $fieldset->addField('psku', 'note', array(
            'label'     => __('SKU'),
            'name'      => 'note',
            'text'     => '<strong>'.$product->getSku().'</strong>',
        ));

        $proId = isset($params['id']) ? $params['id'] : '';
        $stoId = isset($params['store']) ? $params['store'] : '0';


        $fieldset->addField(
            'show_on_online_design_page',
            'hidden',
            [
                'name' => 'show_on_online_design_page'
            ]
        );

//        $fieldset->addField(
//            'temp_show_on_online_design_page',
//            'checkbox',
//            [
//                'name' => 'temp_show_on_online_design_page',
//                'label' => __('Show on Online Design page'),
//                'title' => __('Show on Online Design page'),
//                'required' => false
//            ]
//        );

        $fieldset->addField(
            'design_image',
            'file',
            [
                'name' => 'design_image',
                'label' => __('Design Image'),
                'title' => __('Design Image'),
                'required' => $model->getId() ? false : true,
                'after_element_html' => $this->getImageHtml('design_image', $proId, $stoId)
            ]
        );

        /* onlinedesign grid template */

        $this->_eventManager->dispatch('adminhtml_onlinedesign_edit_tab_main_prepare_form', ['form' => $form]);

        $collectionOnlinedesign = $this->_onlineCollectionFactory->create()
            ->addFieldToFilter("product_id", $id);
        $status = 0;
        $showOnOnlineDesignPage = 0;
        foreach($collectionOnlinedesign as $col){
            $status = $col->getStatus();
            $showOnOnlineDesignPage = $col['show_on_online_design_page'];
            break;
        }

        if($status) {
            $fieldset_t = $form->addFieldset('template_fieldset', ['legend' => __('Onlinedesign Templates')]);

            $renderer = $this->getLayout()->createBlock(
                'Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Templates'
            );

            $fieldset_t->setRenderer($renderer);
        }

        $model->setData('show_on_online_design_page', ($showOnOnlineDesignPage == 1) ? $showOnOnlineDesignPage : 0);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * get Stores by id
     * @param $id
     * @return array
     */
    public function getStoreById($id){
        if($id) {
            $stores = $this->_resourceModel->getStores($id);

            return $stores;
        }
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
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

    /**
     * @param $field
     * @param $image
     * @return string
     */
    protected function getImageHtml($field, $productId, $storeId)
    {
        $onlineImg = $this->_resourceModel->getDesignImage($productId, $storeId);
        $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$onlineImg[0]['design_image'];
        if($onlineImg[0]['design_image'] == 1) {
            $imageUrl = $this->_helper->getImageDefaultProcess();
        }
        $html = '';
        if (!empty($onlineImg[0]) && isset($onlineImg[0]['design_image'])) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<image style="min-width:300px;max-width:100%;" src="' . $imageUrl . '" />';
            $html .= '<input type="hidden" value="' . $onlineImg[0]['design_image'] . '" name="old_' . $field . '"/>';
            $html .= '</p>';
        }

        return $html;
    }
}
