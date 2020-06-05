<?php
/**
 * Onlinedesign data helper
 */

namespace Netbaseteam\Onlinedesign\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MEDIA_PATH = 'nbdesigner';
    const MEDIA_UPLOAD_PATH = 'nbdesigner/uploads';
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory
     */
    protected $httpFactory;

    /**
     * File Uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * File Uploader factory
     *
     * @var \Magento\Framework\Io\File
     */
    protected $_ioFile;
    protected $_dir;
    protected $_storeManager;
    protected $_directory_list;
    protected $_designCollectionFactory;
    protected $_rejectCollectionFactory;
    protected $_colorCollectionFactory;
    protected $_backendUrl;
    protected $_dataConfig;
    protected $_address;
    protected $_layout;
    protected $_catalogSession;
    protected $_assetRepo;
    protected $_customerSession;
    protected $_session;
    protected $_customer;
    protected $_mappingFactory;
    protected $_templateFactory;
    protected $request;
    protected $_productRepository;
    protected $_productCollection;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Address $address,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory $designCollectionFactory,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlinedesignCollectionFactory,
        \Netbaseteam\Onlinedesign\Model\RejectFactory $rejectCollectionFactory,
        \Netbaseteam\Onlinedesign\Model\ColorFactory $colorCollectionFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Netbaseteam\Onlinedesign\Helper\Config $dataConfig,
        \Netbaseteam\Onlinedesign\Model\MappingFactory $mappingFactory,
        \Netbaseteam\Onlinedesign\Model\TemplateFactory $templateFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
    {
        $this->_productCollection = $productCollection;
        $this->_productRepository = $productRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->_templateFactory = $templateFactory;
        $this->_mappingFactory = $mappingFactory;
        $this->_dir = $dir;
        $this->_address = $address;
        $this->_customer = $customer;
        $this->_session = $sessionManager;
        $this->filesystem = $filesystem;
        $this->httpFactory = $httpFactory;
        $this->_ioFile = $ioFile;
        $this->_storeManager = $storeManager;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_directory_list = $directory_list;
        $this->_designCollectionFactory = $designCollectionFactory;
        $this->_onlinedesignCollectionFactory = $onlinedesignCollectionFactory;
        $this->_rejectCollectionFactory = $rejectCollectionFactory;
        $this->_colorCollectionFactory = $colorCollectionFactory;
        $this->_backendUrl = $backendUrl;
        $this->_dataConfig = $dataConfig;
        $this->_layout = $layout;
        $this->_catalogSession = $catalogSession;
        $this->_assetRepo = $assetRepo;
        $this->_customerSession = $customerSession;
        $this->_resourceConnection = $resourceConnection;
        $this->_moduleManager = $moduleManager;
        $this->_objectManager = $objectmanager;
        parent::__construct($context);
    }



    public function getProductUrl($productId){
        $product = $this->_productRepository->getById($productId);
        return $product->getUrlModel()->getUrl($product);
    }

    public function _deleteContent2File()
    {
        $varDir = $this->_directory_list->getPath('var') . '/config/licenseOD.json';
        unlink($varDir);
    }

    public function getLibFolder()
    {
        return $this->_dir->getPath('lib_internal');
    }

    public function getTemplateById($tid) {
        $collection = $this->_templateFactory->create()->getCollection()->addFieldToFilter('product_id', $tid);
        return $collection;
    }

    public function getPopupTemplateLayout() {
        return $this->_layout->createBlock("Netbaseteam\Onlinedesign\Block\CategoryCollection")->setTemplate("Netbaseteam_Onlinedesign::gallery/popup-preview.phtml")->toHtml();
    }

    public function encodeSomething(array $dataToEncode)
    {
        $encodedData = $this->_resultJsonFactory->create()->setData($dataToEncode);

        return $encodedData;
    }

    public function getTemplateId() {
        $fid = $this->_request->getParam('folder');
        $collection = $this->_templateFactory->create()->getCollection()->addFieldToFilter('folder', $fid);
        return $collection;
    }


    public function getTemplateMapping()
    {
        $value = '';
        $mappingData = $this->_mappingFactory->create()->getCollection();
        foreach ($mappingData as $mapping) {
            $connectTo = $mapping->getConnectField();
            if ($connectTo == 'first_name') {
                $value = $this->getFirstNameCustomer();
            } else if ($connectTo == 'last_name') {
                $value = $this->getLastNameCustomer();
            } else if ($connectTo == 'email') {
                $value = $this->getEmailCustomer();
            } else if ($connectTo == 'billing_states' || $connectTo == 'shipping_states') {
                $value = $this->getRegionCustomer();
            } else if ($connectTo == 'billing_website' || $connectTo == 'billing_company' || $connectTo == 'shipping_website' || $connectTo  == 'shipping_company ') {
                $value = $this->getWebsiteCustomer();
            } else if ($connectTo == 'billing_phone' || $connectTo == 'shipping_phone') {
                $value = $this->getTelePhoneCustomer();
            } else if ($connectTo == 'billing_city' || $connectTo == 'shipping_city') {
                $value = $this->getCityCustomer();
            } else if ($connectTo == 'billing-post-code' || $connectTo == 'shipping_post_code') {
                $value = $this->getPostCodeCustomer();
            } else if ($connectTo == 'billing-country' || $connectTo == 'shipping_country') {
                $value = $this->getCountryCustomer();
            }
            $new_fields[] = array_merge($mapping->getData(), array(
                'value' => is_null($value) ? '' : $value,
                'key' => $mapping->getFieldName()
            ));
        }
        return isset($new_fields) ? $new_fields : "" ;
    }

    public function _writeContent2File($content)
    {
        $varDir = $this->_directory_list->getPath('var') . '/config';
        $filePath = $varDir . '/licenseOD.json';
        if (!is_dir($varDir)) {
            mkdir($varDir, 0777, true);
        }
        $myFile = fopen($filePath, 'w+');
        realpath($filePath);

        fwrite($myFile, $content);
        fclose($myFile);
    }

    public function _readFile($pathFile)
    {
        if (!is_dir($this->_directory_list->getPath('var') . '/config')) {
            mkdir($this->_directory_list->getPath('var') . '/config', 0777, true);
            fopen($pathFile, 'w+');
            realpath($pathFile);
        }
        $fp = @fopen($pathFile, "r");
        if (!$fp) {
            fopen($pathFile, 'w+');
            realpath($pathFile);
        }
        return filesize($pathFile) ? fread($fp, filesize($pathFile)) : '';
    }

    public function _getUrlFile($path_file)
    {
        return $this->_assetRepo->getUrl($path_file);
    }

    /**
     * @return bool
     * limit function if license incorrect
     */
    public function releaseLimit()
    {
        $content_license = $this->_readFile($this->_directory_list->getPath('var') . '/config/licenseOD.json');
        if (isset($content_license) && $content_license != "") {
            $arr_license = json_decode($content_license, true);
            if (isset($arr_license['key'])) {
                if ($arr_license['key'] != '') {
                    $resource = $this->_resourceConnection;
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('core_config_data');
                    $sql = "Select * FROM " . $tableName . " where `path`='nb_onlinedesign_license/license/license_code'";
                    $result = $connection->fetchAll($sql);
                    if (count($result) > 0) {
                        $salt = $result[0]['value'];
                        if (!empty($salt)) {
                            if ($salt == $arr_license['key']) {
                                if ($arr_license['type'] == 'free') {
                                    return 'free';
                                }
                                if ($arr_license['type'] == 'pro') {
                                    if ($arr_license['code'] == '3') {
                                        return 'expired';
                                    }
                                    return 'pro';
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param $serializedString
     * @return null|string|string[]
     * fix serialize
     */
    public function serializeCorrector($serializedString)
    {
        // at first, check if "fixing" is really needed at all. After that, security checkup.
        if (@unserialize($serializedString) !== true && preg_match('/^[aOs]:/', $serializedString)) {
            $serializedString = preg_replace_callback('/s\:(\d+)\:\"(.*?)\";/s', function ($matches) {
                return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
            }, $serializedString);
        }
        return $serializedString;
    }

    /**
     * Upload image and return uploaded image file name or false
     *
     * @param string $scope the request key for file
     * @return bool|string
     * @throws Mage_Core_Exception
     */
    public function uploadImage($scope)
    {
        $adapter = $this->httpFactory->create();

        if ($adapter->isUploaded($scope)) {
            // validate image
            if (!$adapter->isValid($scope)) {
                throw new \Magento\Framework\Model\Exception(__('Uploaded image is not valid.'));
            }

            $uploader = $this->_fileUploaderFactory->create(['fileId' => $scope]);
            $uploader->setAllowedExtensions(['svg']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);

            if ($uploader->save($this->getBaseDir())) {
                return $uploader->getUploadedFileName();
            }
        }
        return false;
    }

    public function myUrlEncode($string)
    {
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($entities, $replacements, urlencode($string));
    }

    /**
     * Return the base media directory for Onlinedesign Item images
     *
     * @return string
     */
    public function getBaseDir()
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(self::MEDIA_PATH);
        return $path;
    }

    public function getBaseUploadDir()
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(self::MEDIA_UPLOAD_PATH);
        return $path;
    }

    /**
     * Return the Base URL for Onlinedesign Item images
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . '/' . self::MEDIA_PATH;
    }

    public function getBaseUrlUpload()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . '/' . self::MEDIA_UPLOAD_PATH;
    }

    public function getBaseUrlRoot()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Remove Onlinedesign item image by image filename
     *
     * @param string $imageFile
     * @return bool
     */
    public function removeImage($imageFile)
    {
        $io = $this->_ioFile;
        $io->open(array('path' => $this->getBaseDir()));
        if ($io->fileExists($imageFile)) {
            return $io->rm($imageFile);
        }
        return false;
    }

    public function getLibOnlineDesign()
    {
        return $this->_directory_list->getPath('lib_internal');
    }

    public function getTilteList()
    {
        return "Design: ";
    }

    public function getTitleUpload()
    {
        return "Upload: ";
    }

    public function showButtonDesign()
    {
        if ($this->_dataConfig->isEnableModule()) {
            return $this->_layout->createBlock('Netbaseteam\Onlinedesign\Block\Onlinedesign')
                ->setTemplate('Netbaseteam_Onlinedesign::design_it.phtml')->toHtml();
        }
    }

    public function get_current_user_id()
    {
        $customer_id = 0;
        $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $customer_id = $customerSession->getCustomer()->getId();
        }
        return $customer_id;
    }

    public function getFirstNameCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_customer->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $firstName = isset($data['firstname']) ? $data['firstname'] : "";
        }
        return isset($firstName) ? $firstName : "";
    }

    public function getLastNameCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_customer->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $lastName = isset($data['lastname']) ? $data['lastname'] : "";
        }
        return isset($lastName) ? $lastName : "";
    }



    public function getEmailCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_customer->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $email = isset($data['email']) ? $data['email'] : "";
        }
        return isset($email) ? $email : "";
    }

    public function getAddressCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_address->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $address = isset($data['region']) ? $data['region'] : "";
        }
        return isset($address) ? $address : "";
    }

    public function getRegionCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_address->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $address = isset($data['street']) ? $data['street'] : "";
        }
        return isset($address) ? $address : "";
    }


    public function getCityCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_address->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $city = isset($data['city']) ? $data['city'] : "";
        }
        return isset($city) ? $city : "";
    }

    public function getPostCodeCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_address->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $postCode = isset($data['postcode']) ? $data['postcode'] : "";
        }
        return isset($postCode) ? $postCode : "";
    }

    public function getCountryCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_address->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $country = isset($data['country_id']) ? $data['country_id'] : "";
        }
        return isset($country) ? $country : "";
    }

    public function getTelePhoneCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_address->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $telephone = isset($data['telephone']) ? $data['telephone'] : "";
        }
        return isset($telephone) ? $telephone : "";
    }

    public function getWebsiteCustomer()
    {
        $customerId = $this->get_current_user_id();
        $customer = $this->_address->getCollection()->addFieldToFilter('entity_id', $customerId);
        foreach ($customer as $data) {
            $company = isset($data['company']) ? $data['company'] : "";
        }
        return isset($company) ? $company : "";
    }

    public function plugin_path_data()
    {
        return $this->getBaseDir() . "/";
    }

    public function getRootDir()
    {
        return $this->_directory_list->getRoot();
    }

    public function getMediaPath()
    {
        return $this->getBaseUrl();
    }

    public function nbdesigner_read_json_setting($fullname)
    {
        if (file_exists($fullname)) {
            $list = json_decode(file_get_contents($fullname), true);
        } else {
            $list = '';
            file_put_contents($fullname, $list);
        }
        return $list;
    }

    public function getThumbSize()
    {
        $thumb_size = $this->scopeConfig->getValue('onlinedesign/general/thumb_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $thumb_size;
    }

    public function nbdesigner_save_design_to_image($data, $sid, $pid)
    {
        $links = array();
        $mes = array();
        $order = 'nb_order';

        $path1 = $this->plugin_path_data();
        if (!is_dir($path1)) {
            mkdir($path1, 0777);
        }

        $path2 = $path1 . "/" . 'designs';
        if (!is_dir($path2)) {
            mkdir($path2, 0777);
        }

        $path3 = $path2 . "/" . $sid;
        if (!is_dir($path3)) {
            mkdir($path3, 0777);
        }

        $path4 = $path3 . "/" . $order;
        if (!is_dir($path4)) {
            mkdir($path4, 0777);
        }

        $path5 = $path4 . "/" . $pid;
        if (!is_dir($path5)) {
            mkdir($path5, 0777);
        }

        $path_thumb = $path5 . "/" . 'thumbs';

        if (file_exists($path5)) {
            $this->nbdesigner_delete_folder($path5);
        }

        if (!file_exists($path5)) {

            if (mkdir($path5, 0777)) {
                if (!file_exists($path_thumb)) {
                    if (!mkdir($path_thumb, 0777)) {
                        $mes[] = $this->__('Your server not allow creat folder');
                    }
                }

            } else {
                $mes[] = $this->__('Your server not allow creat folder');
            }
        }

        $thumb_size = explode("x", $this->getThumbSize());
        $width = $thumb_size[0];
        $height = $thumb_size[1];

        foreach ($data as $key => $val) {
            $temp = explode(';base64,', $val);
            $buffer = base64_decode($temp[1]);
            $full_name = $path5 . '/' . $key . '.png';
            if ($this->nbdesigner_save_data_to_image($full_name, $buffer)) {
                $image = file_get_contents($full_name);
                $_width = $width;
                $_height = $height;

                if ($image) {
                    $thumb_file = $path_thumb . '/' . $key . '.png';
                    /* resize image */
                    $src = $this->nbdesigner_resize_imagepng($full_name, $_width, $_height);
                    $image = imagecreatetruecolor($_width, $_height);
                    imagesavealpha($image, true);
                    $color = imagecolorallocatealpha($image, 255, 255, 255, 127);
                    imagefill($image, 0, 0, $color);
                    imagecopy($image, $src, 0, 0, 0, 0, $_width, $_height);
                    imagepng($image, $thumb_file);
                    imagedestroy($src);

                    $links[$key] = $this->nbdesigner_create_secret_image_url($thumb_file);
                }
            } else {
                $mes[] = $this->__('Your server not allow writable file');
            }
        }
        return array('link' => $links, 'mes' => $mes);
    }

    public function nbdesigner_resize_imagepng($file, $w, $h)
    {
        list($width, $height) = getimagesize($file);
        $src = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($w, $h);
        imagesavealpha($dst, true);
        $color = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefill($dst, 0, 0, $color);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
        imagedestroy($src);

        return $dst;
    }

    public function nbdesigner_create_secret_image_url($file_path)
    {
        $type = pathinfo($file_path, PATHINFO_EXTENSION);
        $data = file_get_contents($file_path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    public function nbdesigner_delete_folder($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                $this->nbdesigner_delete_folder(realpath($path) . '/' . $file);
            }
            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }
        return false;
    }

    public function nbdesigner_save_data_to_image($path, $data)
    {
        if (!$fp = fopen($path, 'w')) {
            return FALSE;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        return TRUE;
    }

    public function nbdesigner_copy_dir($src, $dst)
    {
        if (file_exists($dst)) {
            $this->nbdesigner_delete_folder($dst);
        }

        if (is_dir($src)) {
            wp_mkdir_p($dst);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->nbdesigner_copy_dir("$src/$file", "$dst/$file");
                }

            }
        } else if (file_exists($src)) {
            copy($src, $dst);
        }

    }

    public function nbdesigner_list_thumb($path, $level = 2)
    {
        $list = array();
        $_list = $this->nbdesigner_list_files($path, $level);
        $list = preg_grep('/\.(jpg|jpeg|png|gif|pdf)(?:[\?\#].*)?$/i', $_list);
        return $list;
    }

    public function nbdesigner_list_download($path, $level = 2)
    {
        $list = array();
        $_list = $this->nbdesigner_list_files($path, $level);
        $list = preg_grep('/\.(jpg|jpeg|png|gif|svg)(?:[\?\#].*)?$/i', $_list);
        return $list;
    }

    public function nbdesigner_list_files($folder = '', $levels = 100)
    {
        if (empty($folder)) {
            return false;
        }

        if (!$levels) {
            return false;
        }

        $files = array();
        if ($dir = @opendir($folder)) {
            while (($file = readdir($dir)) !== false) {
                if (in_array($file, array('.', '..'))) {
                    continue;
                }
                if (is_dir($folder . '/' . $file)) {
                    $files2 = $this->nbdesigner_list_files($folder . '/' . $file, $levels - 1);
                    if ($files2) {
                        $files = array_merge($files, $files2);
                    } else {
                        $files[] = $folder . '/' . $file . '/';
                    }
                } else {
                    $files[] = $folder . '/' . $file;
                }
            }
        }
        @closedir($dir);
        return $files;
    }

    public function nbdesigner_delete_json_setting($fullname, $id, $reindex = true)
    {
        $list = $this->nbdesigner_read_json_setting($fullname);
        $id_found = $this->indexFound($id, $list, "id");
        if (is_array($list)) {
            array_splice($list, $id_found, 1);
        }
        $res = json_encode($list);
        file_put_contents($fullname, $res);
    }

    public function nbdesigner_delete_gfont_json_setting_bak($fullname, $id, $reindex = true)
    {
        $list = $this->nbdesigner_read_json_setting($fullname);
        if (is_array($list)) {
            array_splice($list, $id, 1);
            if ($reindex) {
                $key = 0;
                $tmp = array();
                $tmp2 = array();
                foreach ($list as $val) {
                    $tmp["name"] = $val["name"];
                    $tmp["id"] = (string)$key;
                    $tmp2[] = $tmp;
                    $key++;
                }
            }
        }
        $res = json_encode($tmp2);
        file_put_contents($fullname, $res);
    }

    public function nbdesigner_delete_gfont_json_setting($fullname, $name, $reindex = true)
    {
        $list = $this->nbdesigner_read_json_setting($fullname);
        $new_list = array();
        if (is_array($list)) {
            $tmp1 = array();
            $tmp11 = array();
            foreach ($list as $val) {
                if ($val["name"] != $name) {
                    $tmp1["name"] = $val["name"];
                    $tmp1["id"] = $val["id"];
                    $tmp11[] = $tmp1;
                }
            }

            $new_list = $tmp11;
            /* reindex id */
            if ($reindex) {
                $key = 0;
                $tmp = array();
                $tmp2 = array();
                foreach ($new_list as $val) {
                    $tmp["name"] = $val["name"];
                    $tmp["id"] = (string)$key;
                    $tmp2[] = $tmp;
                    $key++;
                }
            }
        }
        $res = json_encode($tmp2);
        file_put_contents($fullname, $res);
    }

    public function nbdesigner_update_json_setting_depend($fullname, $id)
    {
        $list = $this->nbdesigner_read_json_setting($fullname);
        $id_found = $this->indexFound($id, $list, "id");
        if (!is_array($list)) {
            return;
        }

        foreach ($list as $val) {
            if (!((sizeof($val) > 0))) {
                continue;
            }

            if (isset($val->cat)) {
                foreach ($val->cat as $k => $v) {
                    if ($v == $id_found) {
                        array_splice($val->cat, $k, 1);
                        break;
                    }
                }
                foreach ($val->cat as $k => $v) {
                    if ($v > $id_found) {
                        $new_v = (string)--$v;
                        unset($val->cat[$k]);
                        array_splice($val->cat, $k, 0, $new_v);
                    }
                }
            }
        }
        $res = json_encode($list);
        file_put_contents($fullname, $res);
    }

    public function nbdesigner_update_json_setting($fullname, $data, $id)
    {
        $list = $this->nbdesigner_read_json_setting($fullname);

        $id_found = $this->indexFound($id, $list, "id");

        if (is_array($list)) {
            $list[$id_found] = $data;
        } else {
            $list = array();
            $list[] = $data;
        }
        $_list = array();
        foreach ($list as $val) {
            $_list[] = $val;
        }
        $res = json_encode($_list);
        file_put_contents($fullname, $res);
    }

    public function indexFound($needle, $haystack, $field)
    {
        if ($haystack == null) {
            return 0;
        }

        foreach ($haystack as $index => $innerArray) {
            if (isset($innerArray[$field]) && $innerArray[$field] === $needle) {
                return $index;
            }
        }
    }

    public function nbdesigner_update_list_arts($art, $id = null)
    {
        $path = $this->plugin_path_data() . "/" . 'arts.json';
        if (isset($id)) {
            $this->nbdesigner_update_json_setting($path, $art, $id);
            return;
        }
        $list_art = array();
        $list = $this->nbdesigner_read_json_setting($path);
        if (is_array($list)) {
            $list_art = $list;
            $art['id'] = (string)$id;
        }
        $list_art[] = $art;
        $res = json_encode($list_art);
        file_put_contents($path, $res);
    }

    public function nbdesigner_get_list_google_font()
    {
        $path = $this->plugin_path_data() . "/" . 'data/listgooglefonts.json';
        $data = (array)$this->nbdesigner_read_json_setting($path);
        return json_encode($data);
    }

    public function nbdesigner_update_font($font, $id)
    {
        $path = $this->plugin_path_data() . "/" . 'fonts.json';
        $this->nbdesigner_update_json_setting($path, $font, $id);
    }

    public function nbdesigner_update_list_fonts($font, $type, $id = null)
    {
        if ($type == "update") {
            $this->nbdesigner_update_font($font, $id);
            return;
        }
        $list_font = array();
        $path = $this->plugin_path_data() . "/" . 'fonts.json';
        $list = $this->nbdesigner_read_json_setting($path);
        if (is_array($list)) {
            $list_font = $list;
        }
        $list_font[] = $font;
        $res = json_encode($list_font);
        file_put_contents($path, $res);
    }

    public function nbdesigner_get_extension($file_name)
    {
        $filetype = explode('.', $file_name);
        $file_exten = $filetype[count($filetype) - 1];
        return $file_exten;
    }

    public function checkFileType($file_name, $arr_mime)
    {
        $check = false;
        $filetype = explode('.', $file_name);
        $file_exten = $filetype[count($filetype) - 1];
        if (in_array(strtolower($file_exten), $arr_mime)) {
            $check = true;
        }

        return $check;
    }

    /*
            Uppercase for first letter of day/month name for the locale
            param $date_str example: jeudi 14 mars 2013
            return string  exp: Jeudi 14 Mars 2013
    */
    public function upppercase_date_string($date_str)
    {
        $date = explode(" ", $date_str);
        $date_arr = array();
        foreach ($date as $d) {
            $date_arr[] = ucfirst($d);
        };
        return implode(" ", $date_arr);

    }

    public function zip_files_and_download($file_names, $archive_file_name, $nameZip)
    {

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if (file_exists($archive_file_name)) {
                unlink($archive_file_name);
            }
            if ($zip->open($archive_file_name, \ZIPARCHIVE::CREATE) !== TRUE) {
                return __("cannot open <$archive_file_name>\n");
            }
            $fCount = 1;
            foreach ($file_names as $file) {
                $path_arr = explode('/', $file);
                $name = $path_arr[count($path_arr) - 1];
                $zip->addFile($file, $name);
                $fCount++;
            }
            $zip->close();
            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename=$nameZip");
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile("$archive_file_name");
            return;
        }
    }

    public function getStatusDesign($pid)
    {
        $onlinedesignModel = $this->_designCollectionFactory->create();
        $model = $onlinedesignModel->getCollection()->addFieldToFilter('product_id', $pid);

        $status = 0;
        foreach ($model as $m) {
            $status = $m->getStatus();
            break;
        }
        return $status;
    }

    public function getStatusUploadDesign($productId)
    {
        if ($this->_request->getParam('task') == 'create' || $this->_request->getParam('task') == 'edit') {
            return 0;
        }
        $collectionDesign = $this->_onlinedesignCollectionFactory->create()->addFieldToFilter('product_id', $productId);
        foreach ($collectionDesign as $collection) {
            return $collection->getStatusUploadDesign();
        }
    }

    public function getDesignLayout($pid)
    {
        $onlinedesignModel = $this->_designCollectionFactory->create();
        $model = $onlinedesignModel->getCollection()->addFieldToFilter('product_id', $pid);

        $status = 0;
        foreach ($model as $m) {
            $status = $m->getUseVisualLayout();
            break;
        }
        return $status;
    }

    public function getOnlinedesignCollection()
    {
        $onlinedesignModel = $this->_designCollectionFactory->create();
        return $onlinedesignModel->getCollection();
    }

    public function getColorCollection()
    {
        $colorModel = $this->_colorCollectionFactory->create();
        return $colorModel->getCollection();
    }

    public function getSessionFolderFromPath($data_design)
    {
        $path_arr_1 = explode("designs", $data_design);
        $path_arr_2 = explode("nb_order", $path_arr_1[1]);
        $folder = trim($path_arr_2[0], '\/');
        return $folder;
    }

    public function _getOnlineDesignByProduct($pid)
    {
        $onlinedesignModel = $this->_designCollectionFactory->create();
        $onlinedesignIds = $onlinedesignModel->getCollection()->addFieldToFilter('product_id', $pid);
        return $onlinedesignIds;
    }

    public function getTemplateUrl()
    {
        return $this->_backendUrl->getUrl('onlinedesign/index/templatelist', ['_current' => true]);
    }

    public function setSessionData($key, $value)
    {
        return $this->_catalogSession->setData($key, $value);
    }

    public function getSessionData($key, $remove = false)
    {
        return $this->_catalogSession->getData($key, $remove);
    }

    public function getMainJsUrl()
    {
        $main_js_url = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::js');
        return $main_js_url;
    }

    public function getImageDefault()
    {
        $defaultImage = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/default.png');
        return $defaultImage;
    }
    public function getImageDefaultProcess()
    {
        $defaultImage = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/default_image.jpg');
        return $defaultImage;
    }

    public function _filterCollection($oid, $pid)
    {
        $collections = $this->_rejectCollectionFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('oid', $oid)
            ->addFieldToFilter('pid', $pid);
        return $collections;
    }

    public function delRecord($oid, $pid)
    {
        $collections = $this->_filterCollection($oid, $pid);
        if (sizeof($collections)) {
            foreach ($collections as $c) {
                $productdesign = $this->_rejectCollectionFactory
                    ->create()
                    ->load($c->getId());
                $productdesign->delete();
            }
        }
        return;
    }

    public function getAction($oid, $pid)
    {
        $collections = $this->_filterCollection($oid, $pid);
        if (sizeof($collections)) {
            foreach ($collections as $c) {
                return $c->getData('action');
            }
        }
        return;
    }

    public function getMaxUploadDesign()
    {
        return $this->scopeConfig->getValue('onlinedesign/main_upload_design/max_upload_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMinUploadDesign()
    {
        return $this->scopeConfig->getValue('onlinedesign/main_upload_design/min_upload_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAllowFileType()
    {
        return $this->scopeConfig->getValue('onlinedesign/main_upload_design/allowed_file_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDisallowFileType()
    {
        return $this->scopeConfig->getValue('onlinedesign/main_upload_design/disallow_file_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getNumberUpload()
    {
        return $this->scopeConfig->getValue('onlinedesign/main_upload_design/number_upload', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public static function delete_folder($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                self::delete_folder(realpath($path) . '/' . $file);
            }
            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }
        return false;
    }

    public static function nbdesigner_resize_imagejpg($file, $w, $h, $path = '')
    {
        list($width, $height) = getimagesize($file);
        if ($path != '') $h = round($w / $width * $height);
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
        imagedestroy($src);
        if ($path == '') {
            return $dst;
        } else {
            imagejpeg($dst, $path);
            imagedestroy($dst);
        }
    }

    public function get_thumb_file($ext, $path = '')
    {
        $thumb = '';
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/file_type/jpg.png');
                break;
            case 'png':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign/images/file_type/png.png');
                break;
            case 'psd':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign/images/file_type/psd.png');
                break;
            case 'pdf':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/file_type/pdf.png');
                break;
            case 'ai':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/file_type/ai.png');
                break;
            case 'eps':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/file_type/eps.png');
                break;
            case 'zip':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/file_type/zip.png');
                break;
            case 'svg':
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/file_type/svg.png');
                break;
            default:
                $thumb = $this->_assetRepo->getUrl('Netbaseteam_Onlinedesign::images/file_type/jpg.png');
                break;
        }
        return $thumb;
    }

    public function nbd_upload_design_file()
    {
        $result = array(
            'flag' => 0,
            'mes' => '',
            'src' => ''
        );
        ob_start();
        $product_id = $_POST['product_id'];
        $upload_setting = unserialize(get_post_meta($product_id, '_nbdesigner_upload', true));
        $allow_ext = explode(',', preg_replace('/\s+/', '', strtolower($this->getAllowFileType())));
        $disallow_ext = explode(',', preg_replace('/\s+/', '', strtolower($this->getDisallowFileType())));
        $ext = strtolower($this->nbdesigner_get_extension($_FILES['file']["name"]));
        $ext = $ext == 'jpeg' ? 'jpg' : $ext;
        $max_size = $this->getMaxUploadDesign() * 1024 * 1024;
        $minsize = $this->getMinUploadDesign() * 1024 * 1024;
        $checkSize = $checkExt = $checkDPI = false;
        if ((count($allow_ext) && $allow_ext[0] != '' && !in_array(strtolower($ext), $allow_ext))
            || (count($disallow_ext) && $disallow_ext[0] != '' && in_array(strtolower($ext), $disallow_ext))) {
            $checkExt = true;
        }
        if ($minsize > $_FILES['file']["size"] || ($max_size != 0 && $max_size < $_FILES['file']["size"])) {
            $checkSize = true;
        }
        if ($checkSize || $checkExt || $checkDPI) {
            if ($checkSize) $result['mes'] = __('File size too small or large!');
            if ($checkExt) $result['mes'] = __('Extension not allowed!');
        } else {
            $rootUrl = $this->_directory_list->getRoot();
            $convertPath = str_replace($rootUrl, $this->getBaseUrlRoot(), $rootUrl);
            $productId = $this->_request->getParam('id');
            $pathUpload = $this->getBaseUploadDir();
            if (!file_exists($pathUpload)) {
                mkdir($pathUpload, 0777, true);
            }
            $session_id = $this->_session->getSessionId();
            $new_name = $_FILES['file']["name"];
            $path_dir = $pathUpload . '/' . $productId . '/' . $session_id . '/' . $new_name;
            $output_dir = $this->getBaseUploadDir() . "/" . $productId . '/' . $session_id . "/";
            if (!file_exists($productId)) wp_mkdir_p($output_dir);
            if (move_uploaded_file($_FILES['file']["tmp_name"], $output_dir . $new_name)) {
                $ret[] = $new_name;
                $jsonFile = $output_dir . $session_id . '.json';
                if (!file_exists($jsonFile)) {
                    file_put_contents($jsonFile, "[]");
                }
                $tmp = array();
                $inp = file_get_contents($jsonFile);
                $tempArray = json_decode($inp);
                $data['order_id'] = "";
                $data['file'] = $productId . "/" . $new_name;
                $data['parent_pid'] = $productId;
                $data['child_pid'] = "";
                $data['comment'] = "";
                $data["child_sku"] = "";
                $tmp[] = $data;
                array_push($tempArray, $tmp);
                $jsonData = json_encode($tempArray);
                file_put_contents($jsonFile, $jsonData);
                $result['mes'] = __('Upload success !');
                $path_preview = str_replace($rootUrl, $convertPath, $path_dir);
                $preview_file = $path_preview;
                $preview_width = 200;
                if ($ext == 'png') {
                    $this->nbdesigner_resize_imagepng($path_dir, $preview_width, $preview_width, $path_dir);
                } else if ($ext == 'jpg') {
                    $this->nbdesigner_resize_imagejpg($path_dir, $preview_width, $preview_width, $path_dir);
                } else {
                    $preview_file = $this->get_thumb_file($ext, $path_dir);
                }
                $src = $preview_file;
                $result['src'] = $src;
                $result['name'] = $new_name;
                $result['flag'] = 1;
            } else {
                $result['mes'] = __('Error occurred with file upload!');
            }
        }
        echo json_encode($result);
    }

    public function redirectCheckoutCart()
    {
        $navigateTo = $this->_objectManager->get('\Magento\Framework\App\Response\Http');
        return $navigateTo->setRedirect('http://127.0.0.1:8888/OnlineDesign/checkout/cart/');
    }

    public function show_nbo_option()
    {
        if (!$this->scopeConfig->getValue('onlinedesign/general/btn_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        $productId = $this->_request->getParam('product_id');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);

        if($product->getTypeId() == 'configurable') return true;

        $customOptions = $this->_objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
        if(count($customOptions)) return true;

        if ($this->_moduleManager->isEnabled('Netbaseteam_PricingOption')) {
            $option_id = $this->_objectManager->create('\Netbaseteam\PricingOption\Model\Option')->getProductOption($productId);
            if($option_id) return true;
        }
        return false;
    }

    public function checkShowOption($productId)
    {
        if (!$this->scopeConfig->getValue('onlinedesign/general/btn_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);

        if($product->getTypeId() == 'configurable') return true;

        $customOptions = $this->_objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
        if(count($customOptions)) return true;

        $option_id = $this->_objectManager->create('\Netbaseteam\PricingOption\Model\Option')->getProductOption($productId);

        if($option_id) return true;
        return false;
    }
}