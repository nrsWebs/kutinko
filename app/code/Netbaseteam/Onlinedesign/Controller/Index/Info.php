<?php
namespace Netbaseteam\Onlinedesign\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Info extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Default Onlinedesign Index page
     *
     * @return void
     */
    public function execute() {

        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class-qrcode.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class.nbdesigner.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class.helper.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class-util.php';

        $nbdesigner = new \Nbdesigner_Plugin();
        $action = $this->getRequest()->getParam('action');

        if ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_get_art) {
            $nbdesigner->nbdesigner_get_art();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_customer_upload) {
            $nbdesigner->nbdesigner_customer_upload();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_upload_design_file) {
            $this->_helper->nbd_upload_design_file();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_copy_image_from_url) {
            $nbdesigner->nbdesigner_copy_image_from_url();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_get_qrcode) {
            $nbdesigner->nbdesigner_get_qrcode();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_get_pattern) {
            $nbdesigner->nbdesigner_get_pattern();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_save_customer_design) {
            $nbdesigner->nbd_save_customer_design();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_editor_html) {
            $nbdesigner->nbd_save_customer_design();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_load_admin_design) {
            $nbdesigner->nbdesigner_load_admin_design();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_get_product_info) {
            $nbdesigner->nbdesigner_get_product_info();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_save_design_to_pdf) {
            $nbdesigner->nbdesigner_save_design_to_pdf();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_get_font) {
            $nbdesigner->nbdesigner_get_font();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_delete_font) {
            $nbdesigner->nbdesigner_delete_font();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_add_google_font) {
            $nbdesigner->nbdesigner_add_google_font();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_frontend_customer_download_pdf) {
            $nbdesigner->nbd_frontend_download_pdf();
        }elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbdesigner_frontend_customer_download_jpg) {
            $nbdesigner->nbd_frontend_download_jpeg();
        }  elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_get_template_preview) {
            $nbdesigner->nbd_get_template_preview();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_get_resource) {
            $nbdesigner->nbd_get_resource();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_get_designs_in_cart) {
            $nbdesigner->nbd_get_designs_in_cart();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_check_use_logged_in) {
            $nbdesigner->nbd_check_use_logged_in();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_save_for_later) {
            $nbdesigner->nbd_save_for_later();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_get_user_designs) {
            $nbdesigner->nbd_get_user_designs();
        } elseif ($action == \Netbaseteam\Onlinedesign\Model\Events::nbd_crop_image) {
            $nbdesigner->nbd_crop_image();
        }
        return;
    }
}
