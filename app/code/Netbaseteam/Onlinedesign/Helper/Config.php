<?php
namespace Netbaseteam\Onlinedesign\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
	/* General */
	
	public function isEnableModule(){
		return $this->scopeConfig->getValue('onlinedesign/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('nb_onlinedesign_license/license/license_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

    public function enableAllowDownloadDesign(){
        return $this->scopeConfig->getValue('onlinedesign/image_options/allow_download_design', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('nb_onlinedesign_license/license/license_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getJpgDownload(){
        return $this->scopeConfig->getValue('onlinedesign/image_options/jpg_download', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('nb_onlinedesign_license/license/license_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPngDownload(){
        return $this->scopeConfig->getValue('onlinedesign/image_options/png_download', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('nb_onlinedesign_license/license/license_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSvgDownload(){
        return $this->scopeConfig->getValue('onlinedesign/image_options/svg_download', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('nb_onlinedesign_license/license/license_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPdfDownload(){
        return $this->scopeConfig->getValue('onlinedesign/image_options/pdf_download', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('nb_onlinedesign_license/license/license_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
	
	public function getDesignLabel(){
		return $this->scopeConfig->getValue('onlinedesign/general/btn_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

    public function getMinDpiUpload(){
        return $this->scopeConfig->getValue('onlinedesign/design_tool/min_dpi_upload', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTemplateMapping(){
        return $this->scopeConfig->getValue('onlinedesign/misc/enable_teamplate_mapping', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	public function getThumbSize(){
		$thumb_size = explode("x", $this->scopeConfig->getValue('onlinedesign/general/thumb_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
		return $thumb_size;
	}
	
	public function getThumbQuality(){
		return $this->scopeConfig->getValue('onlinedesign/general/thumb_quality', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getDefaultDPI(){
		return $this->scopeConfig->getValue('onlinedesign/general/default_dpi', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getUnit(){
		return $this->scopeConfig->getValue('onlinedesign/general/unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getHideOnMobile(){
		return $this->scopeConfig->getValue('onlinedesign/general/hide_on_mobile', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getAdminEmail(){
		return $this->scopeConfig->getValue('onlinedesign/general/owner_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/* Text */
	
	public function EnableAddText(){
		return $this->scopeConfig->getValue('onlinedesign/text_options/enable_add_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getDefaultText(){
		return $this->scopeConfig->getValue('onlinedesign/text_options/default_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function getDefaultColor(){
		return $this->scopeConfig->getValue('onlinedesign/text_options/default_color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	} 
	
	/* Clip Art */
	
	public function EnableClipArt(){
		return $this->scopeConfig->getValue('onlinedesign/clip_art/enable_clipart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	} 
	
	/* Image */
	
	public function EnableAddImage(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_add_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	} 
	
	public function EnableUploadImage(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_upload_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function getLoginRequire(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/login_require', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function getUploadMaxSize(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/upload_max', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function getUploadMinSize(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/upload_min', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function EnableInsertImageUrl(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_image_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function EnableInsertImageFacebook(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_facebook', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function EnableInsertImageGdriver(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_gdriver', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function EnableInsertImagePixabay(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_pixabay', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function EnableInsertImageUnsplash(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_unsplash', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function EnableInsertImageInstagram(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_instagram', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function EnableInsertImageDropbox(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_dropbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getFApiKey(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/facebook_api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function EnableInsertImageWebcame(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/enable_capture_webcame', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}

    public function enableShowLayerSize(){
        return $this->scopeConfig->getValue('onlinedesign/general/show_layer_design', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }
	
	public function EnableInsertImageTerm(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/show_term', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	public function getImageTextTerm(){
		return $this->scopeConfig->getValue('onlinedesign/image_options/term_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	/* Free Draw */
	
	public function EnableFreedraw(){
		return $this->scopeConfig->getValue('onlinedesign/free_draw/enable_free_draw', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}
	
	/* Qr Code */
	
	public function EnableQRCode(){
		return $this->scopeConfig->getValue('onlinedesign/qr_code/enable_qrcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
	}

    /* vCard */
    public function enableVcard(){
        return $this->scopeConfig->getValue('onlinedesign/misc/enable_vcard', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }
	
	public function getQRText(){
		return $this->scopeConfig->getValue('onlinedesign/qr_code/qr_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/* Color */

	public function getShowAllColor(){
		return $this->scopeConfig->getValue('onlinedesign/color/show_all_color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
    public function getStatusOnlineDesign(){
        return $this->scopeConfig->getValue('onlinedesign/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDisplayProductOption() {
    	return $this->scopeConfig->getValue('onlinedesign/product_option/display_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}