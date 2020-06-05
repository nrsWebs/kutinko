require([
  'jquery',
  'jquery/ui'
], function($){

	$("[name=store_id_hidden]").val($("#store_switcher").val());
}); 