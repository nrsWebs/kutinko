<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class DownloadSvg extends \Magento\Backend\App\Action {
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
    ) {
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
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::onlinedesign_manage');
    }

    /**
     * Onlinedesign List action
     *
     * @return void
     */
    public function execute() {
        require_once $this->_dataOnlinedesign->getLibOnlineDesign() . '/Onlinedesign/includes/class-util.php';
        $helper = $this->_dataOnlinedesign;
        $order_id = $this->getRequest()->getParam('order_id');
        $type = $this->getRequest()->getParam('download-type');
        $nbdesigner = new \Nbdesigner_IO;
        ob_start();
        if ($order_id) {
            $zip_files = array();
            $order = $this->_order->load($order_id);
            $order->getAllVisibleItems();
            $orderItems = $order->getItemsCollection()->addAttributeToSelect('*')->load();
            foreach ($orderItems as $item) {
                $sid = $item->getNbdesignerJson();
                if (($sid != null || $sid != "") && $item->getNbdesignerPid()) {
                    $path = $item->getNbdesignerJson();
                    $path_arr = json_decode($path);
                    $re_path = explode("thumbs", $path_arr[0]);
                    $thumb_path_to_zip = $re_path[0];
                    $folder_zip = explode('/preview',$thumb_path_to_zip)[0];
                    $list_images = $nbdesigner->nbdesigner_list_download_svg($folder_zip, 1);
                    if($type == 'svg') {
                        $nbdesigner->convert_svg_embed($folder_zip);
                    }
                    if (count($list_images) > 0) {
                        foreach ($list_images as $key => $image) {
                            $zip_files[] = $image;
                        }
                    }
                }
            }
            $folder_path = $helper->plugin_path_data() . '/downloads';
            $pathZip = $folder_path . '/customer-design-' . $order_id . '.zip';

            if (!is_dir($folder_path)) {
                mkdir($folder_path, 0777, true);
            }

            $nameZip = 'customer-design-' . $order_id . '.zip';
            $helper->zip_files_and_download($zip_files, $pathZip, $nameZip);
        }
    }
}
