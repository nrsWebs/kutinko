<?php
namespace Netbaseteam\Onlinedesign\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Gotocheckout implements ObserverInterface
{
    protected $_productFactory;
    protected $_helper;
    protected $_request;
    protected $_session;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Session\SessionManager $session,
        \Magento\Framework\App\RequestInterface $request,
        \Netbaseteam\Onlinedesign\Helper\Data $helper
    ){
        $this->_productFactory = $productFactory;
        $this->_session = $session;
        $this->_request = $request;
        $this->_helper = $helper;
    }

    public function _getProductData($sku){
        $product = $this->_productFactory->create();
        return $product->loadByAttribute('sku', $sku);
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote_item = $observer->getEvent()->getQuoteItem();
        $productId = $quote_item->getProduct()->getId();

        // save online design in quote_ite
        $dataSession = $this->_session->getData();
        $session_id = $this->_session->getSessionId();

        $pid = (isset($dataSession['nbdesigner_pid']) && $dataSession['nbdesigner_pid']) ? $dataSession['nbdesigner_pid'] : '';
        if($productId == $pid) {

            $design_json_file = (isset($dataSession['nbdesigner_json']) && $dataSession['nbdesigner_json']) ? $dataSession['nbdesigner_json'] : '';
            $quote_item->setData('nbdesigner_sku', $quote_item->getSku());
            $quote_item->setData('nbdesigner_json', $design_json_file);
            $quote_item->setData('nbdesigner_pid', $pid);
            $quote_item->setData('nbdesigner_session', $session_id);

            unset($dataSession['nbdesigner_pid']);
            unset($dataSession['nbdesigner_json']);
        }
        //end

        $product_type = $quote_item->getProductType();
        $parent_pid = $quote_item->getProductId();
        $child_pid = $quote_item->getProductId();
        $child_sku = $quote_item->getSku();
        $output_dir = $this->_helper->getBaseUploadDir()."/".$parent_pid."/";
        $jsonFile = $output_dir.$session_id.'.json';
        if (file_exists($jsonFile)){
            $str    = file_get_contents($jsonFile);
            $rows   = json_decode($str, true);
            for($i=0; $i < count($rows); $i++){
                $tmp = array();$tmp1 = array();
                foreach($rows[$i] as $row) {
                    /* \zend_debug::dump($row["parent_pid"]); */
                    if ($row["parent_pid"] == $parent_pid && $row["child_pid"] == ""){
                        $tmp["order_id"] = "";
                        $tmp["file"] = $row["file"];
                        $tmp["parent_pid"] = $row["parent_pid"];
                        $tmp["child_pid"] = $child_pid;
                        $tmp["comment"] = $row["comment"];
                        $tmp["child_sku"] = $child_sku;
                        $tmp1[] = $tmp;
                    } else {
                        $tmp1[] = $row;
                    }

                }
                $content[]  = $tmp1;
            }
            file_put_contents($jsonFile, json_encode($content));

        }
        $quote_item->setSessionFile($parent_pid."/".$session_id);
        $this->_session->regenerateId();
    }
}
