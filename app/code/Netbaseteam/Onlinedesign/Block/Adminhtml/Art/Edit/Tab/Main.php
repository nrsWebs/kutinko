<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Art\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_catCollection;
    protected $_dataOnlinedesign;

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
		\Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Catart\CollectionFactory $catCollection,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_catCollection = $catCollection;
        $this->_dataOnlinedesign = $dataOnlinedesign;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('art');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::onlinedesign_art')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('art_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Art Information')]);

        if ($model->getId()) {
            $fieldset->addField('art_id', 'hidden', ['name' => 'art_id']);
        }

		$helper = $this->_dataOnlinedesign;
		$result = "";
		$list = $helper->nbdesigner_read_json_setting($helper->plugin_path_data().'arts.json');
		$art_id = $this->getRequest()->getParam('art_id');
		$art_url = "";
		$art_index_found = $helper->indexFound($art_id, $list, "id");
		if (isset($list[$art_index_found])) {
			$art_data = $list[$art_index_found];
			if(isset($art_data["url"])) {
				$art_url  =  $art_data["url"];
				$result = '<img src="'.$helper->getMediaPath()."/cliparts/".$art_data["url"].'" width="100" />';
			}
		}
		

		$fieldset->addField(
            'filename',
            'image',
            [
                'name' => 'filename',
                'label' => __('File'),
                'title' => __('File'),
				'note' => 'Allow extensions: .svg, png, jpg, jpeg',
                'required'  => false
            ]
        );
		
		if($art_url) {
			$fieldset->addField('image', 'note', array(
			  'label'     => __('Preview'),
			  'name'      => 'image',
			  'text'     => $result,
			));
		}
        
		$categories = $this->_catCollection->create();
		$cate_arr = array();
		$cate_arr[''] = '-- Please select caterory --';
	  
		foreach ($categories as $cat):    
			$cate_arr[$cat->getId()] = $cat->getTitle();
		endforeach;
		
		$fieldset->addField(
			'category',
			'select',
			[
				'name' => 'category',
				'label' => __('Select Category'),
				'title' => __('Select Category'),
				'values' => $cate_arr,
				'required'	=> true
			]
		);
		
		$fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );
		
        $this->_eventManager->dispatch('adminhtml_art_edit_tab_main_prepare_form', ['form' => $form]);

		/* show image in form */
		/* if ($model->getFilename()) {
			$path = 'Onlinedesign/'.$model->getFilename();
			$model->setData('filename', $path);
		} */
		
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
        return __('Art Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Art Information');
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
}
