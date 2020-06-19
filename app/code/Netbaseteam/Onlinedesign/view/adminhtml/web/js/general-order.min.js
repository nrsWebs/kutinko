jQuery.noConflict();
jQuery(document).ready(function (jQuery) {
    
    jQuery('#nbdesigner_order_file_submit').on('click', function(e){
        e.preventDefault();
		
		var checked_input = jQuery('#nbdesigner_order_info').find('input').serialize();
		if(checked_input != "") {
			jQuery(this).text("Processing...");
			var formdata = jQuery('#nbdesigner_order_info').find('input, select').serialize();
			var approve_action = jQuery('#nbdesigner_order_info select[name="nbdesigner_order_file_approve"]').val();
			jQuery('#nbdesigner_order_submit_loading').removeClass('nbdesigner_loaded');
			formdata = formdata + '&action=nbdesigner_design_approveAction';
			jQuery.post(admin_nbds.reject_url, formdata, function(data) {
				jQuery('#nbdesigner_order_submit_loading').addClass('nbdesigner_loaded');
				if(data.mes == 'success'){
					jQuery('#nbdesigner_order_info input[class^="nbdesigner_design_file"]:checked').each(function(){
						if (approve_action == 'accept') {
							var newclass = 'approved';
						} else {
							var newclass = 'declined';
						}
						jQuery(this).attr('checked', false);
						jQuery(this).parent('.nbdesigner_container_item_order').attr('class', 'nbdesigner_container_item_order '+newclass);                    
					});
					
					var res = data.change_color.split("|"); 
					jQuery.each(res, function( index, vclass ) {
						if(vclass != "" && data.action == "decline"){
							jQuery(vclass).css("background-color", "#F3E8EE");
						}
						if(vclass != "" && data.action == "accept"){
							jQuery(vclass).css("background-color", "#C3DBF0");
						}
					});
					
				} else {
					alert(data.mes);
				}
			}, 'json');
			jQuery(this).text("Go");
		} else {
			alert("Please select product in checkbox");
		}
    });
});