<?php 
/**
 * Category BOX widget
 * 
 * @category BitVax_Widget
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\Widget\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
 
class CategoryBox extends Template implements BlockInterface
{

    protected $_template = "widget/category_box.phtml";
    /**
     * Category
     *
     * @var Magento\Catalog\Model\Category
     */
    protected $category;
    /**
     * StoreManagerInterface
     *
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Category $category
     * @param array $data
     */
    public function __construct(
        Context $context,
        Category $category,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->category = $category;
        $this->storeManager = $storeManager;
    }

    /**
     * Get category name by category id from widget
     *
     * @return string
     */
    public function getCategorName()
    {
        $categoryID = str_replace('category/', '', $this->getData('category_id'));
        $cat = $this->category->load($categoryID);
        return $cat->getName();
    }

    /**
     * Get image from widget
     *
     * @return string
     */
    public function getImage()
    {
        $currentStore = $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $imageUrl = $mediaUrl . $this->getData('category_image');
        return $imageUrl;
    }
}