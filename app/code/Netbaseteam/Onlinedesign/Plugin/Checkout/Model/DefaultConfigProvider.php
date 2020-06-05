<?php
namespace Netbaseteam\Onlinedesign\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;

class DefaultConfigProvider
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Netbaseteam\Onlinedesign\Helper\Integrate
     */
    protected $helperOnlineDesign;

    /**
     * DefaultConfigProvider constructor.
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Netbaseteam\Onlinedesign\Helper\Integrate $helperOnlineDesign
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Netbaseteam\Onlinedesign\Helper\Integrate $helperOnlineDesign,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->layoutFactory = $layoutFactory;
        $this->helperOnlineDesign = $helperOnlineDesign;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * add new output orderUpload
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    )
    {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $result['quoteItemData'][$index]['onlineDesign'] = $this->helperOnlineDesign->showVirtualProductDesign($quoteItem);
            $result['quoteItemData'][$index]['uploadDesign'] = $this->helperOnlineDesign->getUploadCustomer($quoteItem);
            if($result['quoteItemData'][$index]['onlineDesign'])
            $result['quoteItemData'][$index]['buttonEditDesign'] = $this->helperOnlineDesign->renderButtonEditDesign($quoteItem);
        }
        return $result;
    }
}