<script type='text/javascript'>
			
	jQuery(document).bind("gform_load_field_settings", function(event, field, form){
						
		if(field.type == 'gse_date' ) {
			
			setTimeout(function() {
				var select_val = typeof field["gse_type"] != "undefined" ? field["gse_type"] : "select";
				jQuery("#gse_type").val(select_val);
			}, 100);					
			
		}
	
	});
	
	jQuery(document).bind("gform_load_field_choices", function(event, field){
		
		var end_date_type = '<?php echo esc_html ( $end_date_type ); ?>';
		var end_type = '<?php echo esc_html ( $end_type ); ?>';					
		
		if( ! end_type ) {
			end_type = 'both_types';
		}
		
		setTimeout(function() {
			// console.log(123);
			// jQuery('#gse_type').val(field['gse_type'] == undefined ? "select" : field['gse_type']);
		}, 300);
		// console.log( '123' ); 
		console.log( end_type ); 
		// console.log( end_date_type ); 
		if( end_type && ( field.type == 'gse_date' || field.type == 'gfsd_date') ) {
			
			var end_date_type = '<?php echo esc_html ( $end_date_type ); ?>';					
			if( end_date_type == 'both_types' && end_type == "select_dates" ) {
				jQuery("#field_choice_values_enabled").parent().find("label").html("Show End Date");	
				
				setTimeout(function() {
					jQuery("#field_choice_values_enabled").prop("checked", true); 					
				}, 300);
			}
			else {
				jQuery("#field_choice_values_enabled").parents('.choices-ui__section').hide();			
				
				setTimeout(function() {
					jQuery("#field_choice_values_enabled").prop("checked", false); 					
				}, 300);
			}
			
			setTimeout(function() {
				SetFieldProperty('enableChoiceValue', 1);						
			}, 400);
			
			setTimeout(function() {
				ToggleChoiceValue(); 
			}, 500);
			
			setTimeout(function() {
				SetFieldChoices();
			}, 600);
			
			jQuery("#gfield_settings_choices_container .gfield_choice_header_label").html("Add Date(s)");
			jQuery("#gfield_settings_choices_container .gfield_choice_header_value").html("Add Date(s)");
			
			// jQuery("li[data-input_type='"+field.type+"'] .field-choice-text").attr("readonly", true);					
			// jQuery("li[data-input_type='"+field.type+"'] .field-choice-value").attr("readonly", true);					
			
			/*jQuery("li[data-input_type='"+field.type+"'] .field-choice-text").datepicker({ 
				minDate: 0,
				dateFormat: 'M d, yy',
				onSelect: function(dateText) {
					jQuery("li[data-input_type='"+field.type+"'] .field-choice-text").trigger("propertychange");
				}
			});	
			
			jQuery("li[data-input_type='"+field.type+"'] .field-choice-value").datepicker({ 
				minDate: 0,
				dateFormat: 'M d, yy',
				onSelect: function(dateText) {
					jQuery("li[data-input_type='"+field.type+"'] .field-choice-value").trigger("propertychange");
				}
			});	*/
			jQuery("li[data-input_type='"+field.type+"'] .field-choice-text").attr( "type", "date" );
			jQuery("li[data-input_type='"+field.type+"'] .field-choice-value").attr( "type", "date" );
			
		}
		else {
			jQuery("#field_choice_values_enabled").parent().show();	
		}
		
		jQuery('#gse_type').val(field['gse_type'] == undefined ? "select" : field['gse_type']);
		if(field.type == 'gfsd_date' && field.gse_type == 'datepicker' ) {
			
			setTimeout(function() {
				jQuery('.choices-ui__trigger-section').hide();
				
			}, 200);
			
		}
		else if( field.type == 'gfsd_date' ) {
			setTimeout(function() {
				jQuery('.choices-ui__trigger-section').show();
			}, 200);
		}
		
		// console.log(field);
		// console.log(field['gse_type']);
		jQuery('#gse_type').val(field['gse_type'] == undefined ? "select" : field['gse_type']);
	});
	
	jQuery('#gform_fields').on('click', '.gfield', function () {
		var field = this;
		//console.log(field.id);
	});
	
	
</script>

<script type="text/javascript">

function SetDateInputType(type){ console.log('123');
    field = GetSelectedField();
    if(GetInputType(field) != "gse_date_picker" && GetInputType(field) != "date")
        return;

	if ( type === 'datepicker' ) {
		SetFieldAccessibilityWarning( 'date_input_type_setting', 'above' );
	} else {
		resetAllFieldAccessibilityWarnings();
	}

    field.dateType = type;
    field.inputs = GetDateFieldInputs(field);

    CreateDefaultValuesUI(field);
    CreatePlaceholdersUI(field);
    CreateInputLabelsUI(field);
    ToggleDateSettings(field);

    ResetDefaultInputValues(field);

    ToggleDateCalendar();
    LoadDateInputs();
}

function SetCalendarIconType(iconType, isInit){
    field = GetSelectedField();
    if(GetInputType(field) != "gse_date_picker" && GetInputType(field) != "date")
        return;

    if(iconType == undefined)
        iconType = "none";

    if(iconType == "none")
        jQuery("#gsetting_icon_none").prop("checked", true);
    else if(iconType == "calendar")
        jQuery("#gsetting_icon_calendar").prop("checked", true);
    else if(iconType == "custom")
        jQuery("#gsetting_icon_custom").prop("checked", true);

    SetFieldProperty('calendarIconType', iconType);
    ToggleCalendarIconUrl( isInit );
    LoadDateInputs();
}
</script>