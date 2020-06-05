<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class DownloadUpload extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_dataOnlinedesign;
    protected $_order;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
        \Magento\Sales\Model\Order $order,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_order = $order;
        $this->_dataOnlinedesign = $dataOnlinedesign;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::onlinedesign_manage');
    }

    /**
     * Onlinedesign List action
     *
     * @return void
     */
    public function execute()
    {
        $helper = $this->_dataOnlinedesign;
        $order_id = $this->getRequest()->getParam('order_id');
        $download_all = $this->getRequest()->getParam('download-all');
        ob_start();
        if ($order_id) {
            if ($download_all) {
                $zip_files = array();
                $order = $this->_order->load($order_id);
                $order->getAllVisibleItems();
                $orderItems = $order->getItemsCollection()->addAttributeToSelect('*')->load();
                foreach ($orderItems as $item) {
                    $path = $item->getSessionFile();
                    $dataFile = $helper->getBaseUploadDir() . '/' . $path;
                    $list_images = $helper->nbdesigner_list_thumb($dataFile, 1);
                    if (count($list_images) > 0) {
                        foreach ($list_images as $key => $image) {
                            $zip_files[] = $image;
                        }
                    }
                }
                $folder_path = $helper->plugin_path_data() . '/downloads';
                $pathZip = $folder_path . '/customer-upload-' . $order_id . '.zip';

                if (!is_dir($folder_path)) {
                    mkdir($folder_path, 0777, true);
                }

                $nameZip = 'customer-upload-' . $order_id . '.zip';
                $helper->zip_files_and_download($zip_files, $pathZip, $nameZip);
            }
        }
    }
}
