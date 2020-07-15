<?php

namespace Netbaseteam\Onlinedesign\Helper;

class Integrate extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_dataConfig;
    protected $_dataData;
    protected $_checkoutSession;
    protected $_quoteFactory;
    public $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Netbaseteam\Onlinedesign\Helper\Config $dataConfig,
        \Netbaseteam\Onlinedesign\Helper\Data $dataData
    )
    {
        $this->_storeManager=$storeManager;
        $this->_dataConfig = $dataConfig;
        $this->_dataData = $dataData;
        $this->_quoteFactory = $quoteFactory;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function getUploadCustomer($_item)
    {
        if ($this->_dataConfig->isEnableModule()) {
            $session = $this->_checkoutSession;
            $quote_id = $session->getQuoteId();
            $quote_item_id = $_item->getId();
            $quote = $this->_quoteFactory->create()->load($quote_id);
            $quote->getAllVisibleItems();
            $quoteItems = $quote->getItemsCollection();
            foreach ($quoteItems as $qItem) {
                if ($qItem->getId() == $quote_item_id && $qItem->getSessionFile() != "") {
                    $sessionFile = $qItem->getSessionFile();
                    $replaceFileName = str_replace($qItem->getProductId() . '/', '', $sessionFile);
                    $output_dir = $this->_dataData->getBaseUploadDir() . "/" . $sessionFile . '/' . $replaceFileName;
                    $jsonFile = $output_dir . '.json';
                    if (file_exists($jsonFile)) {
                        $str = file_get_contents($jsonFile);
                        $rows = json_decode($str, true);
                        $html = $this->_dataData->getTitleUpload() . '<br /><div>';
                        for ($i = 0; $i < count($rows); $i++) {
                            foreach ($rows[$i] as $row) {
                                $ext = strtolower($this->_dataData->nbdesigner_get_extension($row['file']));
                                $replaceFileNameUpload = str_replace($qItem->getProductId() . '/', '', $row['file']);
                                $dataFile = $this->_dataData->getBaseUrlUpload() . '/' . $sessionFile . '/' . $replaceFileNameUpload;
                                if ($ext == 'psd' || $ext == 'pdf' || $ext == 'ai' || $ext == 'eps' || $ext == 'zip' || $ext == 'svg') {
                                    $checkFiles = $this->_dataData->get_thumb_file($ext, $dataFile);
                                    $html .= '<a target="_blank" href="' . $dataFile . '"> <img width="60" height="60" style="border-radius: 3px; border: 1px solid #ddd; margin-top: 5px; margin-right: 5px; display: inline-block;" src="' . $checkFiles . '"/> </a>';
                                } else {
                                    $html .= '<a target="_blank" href="' . $dataFile . '"> <img width="60" height="60" style="border-radius: 3px; border: 1px solid #ddd; margin-top: 5px; margin-right: 5px; display: inline-block;" src="' . $dataFile . '"/> </a>';
                                }

                            }
                        }
                        return $html;
                    }
                }
            }
        }
    }

    public function getUploadOrder($_item)
    {
        $objectManagerr = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManagerr->create('\Magento\Sales\Model\Order')->load($_item->getOrderId());
        $order->getAllVisibleItems();
        $orderItems = $order->getItemsCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('eq' => $_item->getSku()))
            ->load();
        foreach ($orderItems as $qItem) {
            if ($qItem->getSessionFile() != "") {
                $sessionFile = $qItem->getSessionFile();
                $replaceFileName = str_replace($qItem->getProductId() . '/', '', $sessionFile);
                $output_dir = $this->_dataData->getBaseUploadDir() . "/" . $sessionFile . '/' . $replaceFileName;
                $jsonFile = $output_dir . '.json';
                if (file_exists($jsonFile)) {
                    $str = file_get_contents($jsonFile);
                    $rows = json_decode($str, true);
                    $html = $this->_dataData->getTitleUpload() . '<br /><div>';
                    for ($i = 0; $i < count($rows); $i++) {
                        foreach ($rows[$i] as $row) {
                            $ext = strtolower($this->_dataData->nbdesigner_get_extension($row['file']));
                            $replaceFileNameUpload = str_replace($qItem->getProductId() . '/', '', $row['file']);
                            $dataFile = $this->_dataData->getBaseUrlUpload() . '/' . $sessionFile . '/' . $replaceFileNameUpload;
                            if ($ext == 'psd' || $ext == 'pdf' || $ext == 'ai' || $ext == 'eps' || $ext == 'zip' || $ext == 'svg') {
                                $checkFiles = $this->_dataData->get_thumb_file($ext, $dataFile);
                                $html .= '<a target="_blank" href="' . $dataFile . '"> <img width="60" height="60" style="border-radius: 3px; border: 1px solid #ddd; margin-top: 5px; margin-right: 5px; display: inline-block;" src="' . $checkFiles . '"/> </a>';
                            } else {
                                $html .= '<a target="_blank" href="' . $dataFile . '"> <img width="60" height="60" style="border-radius: 3px; border: 1px solid #ddd; margin-top: 5px; margin-right: 5px; display: inline-block;" src="' . $dataFile . '"/> </a>';
                            }

                        }
                    }
                    return $html;
                }
            }
        }
    }



    public function showVirtualProductDesign($_item)
    {
        /* online product design */
        $data_design = "";
        if ($this->_dataConfig->isEnableModule()) {
            $src_json_arr = array();
            $session = $this->_checkoutSession;
            $quote_id = $session->getQuoteId();
            $quote_item_id = $_item->getId();
            $quote = $this->_quoteFactory->create()->load($quote_id);
            $quote->getAllVisibleItems();
            $quoteItems = $quote->getItemsCollection();
            foreach ($quoteItems as $qItem) {
                if ($qItem->getId() == $quote_item_id && $qItem->getNbdesignerJson() != "") {
                    $data_design = $qItem->getNbdesignerJson();
                }
            }
            $list = json_decode($data_design);
            if ($data_design != "") {
                $html = "";
                if ($list) {
                    $html = $this->_dataData->getTilteList() . '<br /><div>';
                    $iData = $_item->getProduct();
                    foreach ($list as $img) {
                        $url = $this->convert_path_to_url($img);
                        $src = str_replace('thumbs', 'preview', $url);
                        $realtime = $src . '?t=' .time();
                        $html .= '<img width="200" height="200" style="border-radius: 3px; border: 1px solid #ddd; margin-top: 5px; margin-right: 5px; display: inline-block;" src="' . $realtime . '"/>';
                    }
                    $html .= '</div>';
                }
                return $html;
            }
        }
    }
    public function renderButtonEditDesign($_item)
    {
        $visualLayout = $this->_dataData->getDesignLayout($_item->getProduct()->getProductId());
        if($visualLayout == 1) {
        $linkEditDesign = $_item->getProduct()->getProductUrl();
        $linkEditDesign .= "?nbdv-task=edit-checkout&product_id=" . $_item->getProduct()->getProductId();
        $linkEditDesign .= '&nbd_item_key=' . $this->getNbSessionId($_item) . '&md=co';
        $textEdit = __('Edit Design');
        $html = "";
        $html .= '<a class="button nbd-edit-design" href="' . $linkEditDesign . '"> '.$textEdit.' </a>';
        return $html;
        }else {
            $linkEditDesign = $this->_storeManager->getStore()->getBaseUrl() . 'onlinedesign/index/design';
            $linkEditDesign .= "?task=edit&product_id=" . $_item->getProductId();
            $linkEditDesign .= '&nbd_item_key=' . $this->getNbSessionId($_item) . '&md=co';
            $textEdit = __('Edit Design');
            $html = "";
            $html .= '<a class="button nbd-edit-design" href="' . $linkEditDesign . '"> '.$textEdit.' </a>';
            return $html;
        }
    }
    public function convert_path_to_url($path)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue("web/unsecure/base_url");
        $path_dir = explode('media', $path);
        return $baseUrl . 'pub/media/' . $path_dir[1];
    }

    public function getNbSessionId($_item){
        $session = $this->_checkoutSession;
        $quote_id = $session->getQuoteId();
        $quote_item_id = $_item->getId();
        $quote = $this->_quoteFactory->create()->load($quote_id);
        $quote->getAllVisibleItems();
        $quoteItems = $quote->getItemsCollection();
        foreach ($quoteItems as $qItem) {
            if ($qItem->getId() == $quote_item_id && $qItem->getNbdesignerJson() != "") {
                $data_design = $qItem->getNbdesignerSession();
            }
        }
        return isset($data_design) ? $data_design : "";
    }

    /**
     * show online design attach file on admin order item
     * @param $_item
     * @return string
     */
    public function showOnlineDesignAttachFile($_item)
    {
        $html = "";
        if ($data_design = $_item['nbdesigner_json']) {
            $html = $this->_dataData->getTilteList() . '<br /><div>';
            foreach (json_decode($data_design) as $img) {
                $url = $this->convert_path_to_url($img);
                $src = str_replace('thumbs', 'preview', $url);
                $html .= '<img width="60" height="60" style="border-radius: 3px; border: 1px solid #ddd; margin-top: 5px; margin-right: 5px; display: inline-block;" src="' . $src . '"/>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    public function showDesignOrder($_item)
    {
        $objectManagerr = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManagerr->create('\Magento\Sales\Model\Order')->load($_item->getOrderId());
        $order->getAllVisibleItems();
        $orderItems = $order->getItemsCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('eq' => $_item->getSku()))
            ->load();
        $data_design = "";
        foreach ($orderItems as $sItem) {
            if (($sItem->getNbdesignerJson() != null || $sItem->getNbdesignerJson() != "") && $sItem->getNbdesignerSku() == $_item->getSku()) {
                $data_design = $sItem->getNbdesignerJson();
            }
        }
        $helper = $this->_dataData;
        if ($data_design != "") {
            $html = $helper->getTilteList() . '<br /><div>';
            $list = json_decode($data_design);
            foreach ($list as $img) {
                $url = $this->convert_path_to_url($img);
                $src = str_replace('thumbs', 'preview', $url);
                $html .= '<img width="60" height="60" style="border-radius: 3px; border: 1px solid #ddd; margin-top: 5px; margin-right: 5px; display: inline-block;" src="' . $src . '"/>';
            }
            $html .= '</div>';
            return $html;
        }
    }
}