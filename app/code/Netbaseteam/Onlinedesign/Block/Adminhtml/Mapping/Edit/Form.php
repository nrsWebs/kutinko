<?php

namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Mapping\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Netbaseteam\Onlinedesign\Model\Status $options,
        array $data = []
    )
    {
        $this->_options = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {

        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
            ]
        );
        $form->setHtmlIdPrefix('nb_mapping_');
        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Item Data'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('template_mapping_id', 'hidden', ['name' => 'template_mapping_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Field'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'field_name',
            'text',
            [
                'name' => 'field_name',
                'label' => __('Field Name'),
                'id' => 'field_name',
                'title' => __('Field Name'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );
        $fieldConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Netbaseteam\Onlinedesign\Model\Config\Source\OptionsField');
        $fieldset->addField(
            'connect_field',
            'select',
            [
                'name' => 'connect_field',
                'label' => __('Connect Field To'),
                'required' => true,
                'values' => $fieldConfig->toOptionArray()
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
