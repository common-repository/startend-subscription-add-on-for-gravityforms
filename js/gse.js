jQuery(document).ready(function() {
	
	gse_perform_hide_show();
	
	jQuery("#enable_gse_end_date, #gse_end_date_type, #enable_gse, #gse_field_type").change(function(){
		gse_perform_hide_show();
	});
	
	gform.addFilter( 'gform_datepicker_options_pre_init', function( optionsObj, formId, fieldId ) {

		var id = '#input_' + formId + '_' + fieldId;
		if( jQuery(id).length && jQuery(id).parents('.gse_date').length ) {
			optionsObj.minDate = 0;
		}
		
		return optionsObj;
	});
});

function gse_perform_hide_show() {
	
	//jQuery("#gaddon-setting-row-gse_end_date_type").parents(".gaddon-section").hide();
	// jQuery("#gaddon-setting-row-gse_end_date_type").hide();
	jQuery("#gaddon-setting-row-gse_end_date_type_duration").show();
	jQuery("#gform_setting_gse_end_date_type_duration").show();	
	
	jQuery("#gaddon-setting-row-gse_end_date_type_payment_number").show();
	jQuery("#gform_setting_gse_end_date_type_payment_number").show();	
	
	jQuery("#gaddon-setting-row-gse_map_end").show();	
	jQuery("#gform_setting_gse_map_end").show();	
	
	jQuery("#gaddon-setting-row-gse_end_type").show();
	jQuery("#gform_setting_gse_end_type").show();
	
	if( jQuery("#enable_gse_end_date").length > 0 ) {
		
		if( ! jQuery("#enable_gse_end_date").prop("checked") ) { 
			jQuery("#gaddon-setting-row-gse_end_date_type_duration").hide();
			jQuery("#gaddon-setting-row-gse_end_date_type_payment_number").hide();
			jQuery("#gaddon-setting-row-gse_map_end").hide();
			
			jQuery("#gform_setting_gse_end_date_type_duration").hide();
			jQuery("#gform_setting_gse_end_date_type_payment_number").hide();
			jQuery("#gform_setting_gse_map_end").hide();
			
		}		
	}
	
	if( jQuery("#gse_field_type").length > 0 ) {
		
		var gse_field_type = jQuery("#gse_field_type").val();
		if( gse_field_type != "both_types" && gse_field_type != "end_date_only"  ) { 
			jQuery("#gaddon-setting-row-gse_end_type").hide();
			jQuery("#gform_setting_gse_end_type").hide();
			
		}		
	}
	
	
	jQuery("#gaddon-setting-row-gse_map").show();
	jQuery("#gform_setting_gse_map").show();
	if( jQuery("#enable_gse").length > 0 ) {
		
		if( ! jQuery("#enable_gse").prop("checked") ) { 
			jQuery("#gaddon-setting-row-gse_map").hide();			
			jQuery("#gform_setting_gse_map").hide();			
		}		
	}
}