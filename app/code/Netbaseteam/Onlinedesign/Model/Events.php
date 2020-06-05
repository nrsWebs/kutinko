<?php
namespace Netbaseteam\Onlinedesign\Model;

class Events {
	const nbdesigner_get_art = "nbdesigner_get_art";
	const nbdesigner_customer_upload = "nbdesigner_customer_upload";
	const nbdesigner_copy_image_from_url = "nbdesigner_copy_image_from_url";
	const nbdesigner_get_qrcode = "nbdesigner_get_qrcode";
	const nbd_upload_design_file = "nbd_upload_design_file";
	const nbdesigner_get_pattern = "nbdesigner_get_pattern";
	const nbd_save_customer_design = "nbd_save_customer_design";
	const nbd_get_template_preview = "nbd_get_template_preview";
	const nbdesigner_editor_html = "nbdesigner_editor_html";
	const nbdesigner_load_admin_design = "nbdesigner_load_admin_design";
	const nbdesigner_get_product_info = "nbdesigner_get_product_info";
	const nbdesigner_save_design_to_pdf = "nbdesigner_save_design_to_pdf";
	const nbdesigner_frontend_customer_download_pdf = "nbd_frontend_download_pdf";
	const nbdesigner_frontend_customer_download_jpg = "nbd_frontend_download_jpeg";
	const nbdesigner_save_design_to_png = "nbdesigner_save_design_to_png";
	const nbdesigner_get_font = "nbdesigner_get_font";
	const nbdesigner_delete_font = "nbdesigner_delete_font";
	const nbdesigner_add_google_font = "nbdesigner_add_google_font";
	const nbd_get_resource = "nbd_get_resource";
	const nbd_get_designs_in_cart = "nbd_get_designs_in_cart";
	const nbd_check_use_logged_in = "nbd_check_use_logged_in";
	const nbd_save_for_later = "nbd_save_for_later";
	const nbd_get_user_designs = "nbd_get_user_designs";
	const nbd_crop_image = "nbd_crop_image";

	public function nbdesigner_ajax() {
		// Nbdesigner_EVENT => nopriv
		$ajax_events = array(
			'nbdesigner_add_font_cat' => false,
			'nbdesigner_add_art_cat' => false,
			'nbdesigner_add_google_font' => false,
			'nbdesigner_delete_font_cat' => false,
			'nbdesigner_delete_art_cat' => false,
			'nbdesigner_delete_font' => false,
			'nbdesigner_delete_art' => false,
			'nbdesigner_get_product_info' => true,
			'nbdesigner_save_customer_design' => true,
			'nbdesigner_get_qrcode' => true,
			'nbdesigner_get_facebook_photo' => true,
			'nbdesigner_get_art' => true,
			'nbdesigner_design_approve' => false,
			'nbdesigner_design_order_email' => false,
			'nbdesigner_customer_upload' => true,
			'nbdesigner_get_font' => true,
			'nbdesigner_get_pattern' => true,
			'nbdesigner_get_info_license' => false,
			'nbdesigner_remove_license' => false,
			'nbdesigner_get_license_key' => false,
			'nbdesigner_get_security_key' => false,
			'nbdesigner_get_language' => true,
			'nbdesigner_save_language' => false,
			'nbdesigner_create_language' => false,
			'nbdesigner_make_primary_design' => false,
			'nbdesigner_load_admin_design' => true,
			'nbdesigner_save_webcam_image' => true,
			'nbdesigner_migrate_domain' => false,
			'nbdesigner_restore_data_migrate_domain' => false,
			'nbdesigner_theme_check' => false,
			'nbdesigner_custom_css' => false,
			'nbdesigner_copy_image_from_url' => true,
			'nbdesigner_get_suggest_design' => true,
			'nbdesigner_save_design_to_pdf' => false,
			'nbdesigner_save_design_to_png' => false,
			'nbdesigner_save_partial_customer_design' => true,
			'nbdesigner_save_customer_design2' => true,
			'nbdesigner_delete_language' => false,
			'nbdesigner_update_all_product' => false,
			'nbd_save_customer_design' => true,
			'nbd_get_resource' => true,
			'nbd_get_designs_in_cart' => true,
			'nbd_check_use_logged_in' => true,
			'nbd_save_for_later' => true,
			'nbd_get_user_designs' => true,
			'nbd_crop_image' => true
		);
		foreach ($ajax_events as $ajax_event => $nopriv) {
			add_action('wp_ajax_' . $ajax_event, array($this, $ajax_event));
			if ($nopriv) {
				/* NBDesigner AJAX can be used for frontend ajax requests */
				add_action('wp_ajax_nopriv_' . $ajax_event, array($this, $ajax_event));
			}
		}

		return $ajax_events;
	}
}