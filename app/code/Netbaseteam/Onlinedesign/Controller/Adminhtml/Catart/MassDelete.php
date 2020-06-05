<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Catart;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Netbaseteam\Onlinedesign\Model\ResourceModel\Catart\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action {
	/**
	 * @var Filter
	 */
	protected $filter;

/**
 * @var CollectionFactory
 */
	protected $collectionFactory;

	protected $_catart;

	protected $_art;

	protected $_helper;

/**
 * @param Context $context
 * @param Filter $filter
 * @param CollectionFactory $collectionFactory
 */
	public function __construct(
		Context $context,
		Filter $filter,
		CollectionFactory $collectionFactory,
		\Netbaseteam\Onlinedesign\Helper\Data $helper,
		\Netbaseteam\Onlinedesign\Model\Art $art,
		\Netbaseteam\Onlinedesign\Model\Catart $catart
	) {
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		$this->_helper = $helper;
		$this->_art = $art;
		$this->_catart = $catart;
		parent::__construct($context);
	}

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public function execute() {
		$catIds = $this->getRequest()->getParam('catart');
		if (!is_array($catIds) || empty($catIds)) {
			$this->messageManager->addError(__('Please select category art(s).'));
		} else {
			try {
				foreach ($catIds as $catId) {
					/* delete all art of categories */
					$modelArt = $this->_art->getCollection()->addFieldToFilter("category", $catId);

					foreach ($modelArt as $m) {
						$modelArtDel = $this->_art->load($m->getId());
						$this->nbdesigner_delete_art($m->getId());
						$modelArtDel->delete();
					}

					$cat = $this->_catart->load($catId);
					$cat->delete();
					$this->nbdesigner_delete_art_cat($catId);
				}
				$this->messageManager->addSuccess(
					__('A total of %1 record(s) have been deleted.', count($catIds))
				);
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}

	public function nbdesigner_delete_art_cat($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
		$path = $helper->plugin_path_data() . 'art_cat.json';
		$helper->nbdesigner_delete_json_setting($path, $id, false);
		$art_path = $helper->plugin_path_data() . 'arts.json';
		$helper->nbdesigner_update_json_setting_depend($art_path, $id);
	}

	public function nbdesigner_delete_art($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
		$path = $helper->plugin_path_data() . 'arts.json';
		$list = $helper->nbdesigner_read_json_setting($path);
		$id_found = $helper->indexFound($id, $list, "id");
		$file_art = $helper->plugin_path_data() . $list[$id_found]["file"];
		try {
			unlink($file_art);
		} catch (\Exception $e) {}
		$helper->nbdesigner_delete_json_setting($path, $id);
	}
}