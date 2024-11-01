<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GSE_Field extends GF_Field {

	public $type = 'gfsd_date';
	
	public function get_form_editor_field_title() {
		return esc_attr__( 'Start / End Date(s)', 'gse' );
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'gravitystripe_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	public function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'enable_enhanced_ui_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'size_setting',
			'gse-choices',
			'gse_type',
			'choices_setting',			
			'date_format_setting',
			'rules_setting',
			'placeholder_setting',
			'default_value_setting',
			'visibility_setting',
			'duplicate_setting',
			'description_setting',
			'css_class_setting',
		);
	}

	public function is_conditional_logic_supported() {
		return true;
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$id       = $this->id;
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$size               = $this->size;
		$class_suffix       = $is_entry_detail ? '_admin' : '';
		$class              = $size . $class_suffix;
		$css_class          = trim( esc_attr( $class ) . ' gfield_select' );
		$tabindex           = $this->get_tabindex();
		$disabled_text      = $is_form_editor ? 'disabled="disabled"' : '';
		$required_attribute = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		
		$gse_type = $this->gse_type ? $this->gse_type : "select";
		// $gse_type = "select";
		
		if ($gse_type == "radio") {
			return sprintf( "<div class='ginput_container ginput_container_radio'><ul class='gfield_radio' id='%s'>%s</ul></div>", $field_id, $this->get_radio_choices( $value, $disabled_text, $form_id ) );
		}
		else if( $gse_type == "datepicker" ) {
			
			$picker_value = '';
			if ( is_array( $value ) ) {
				$value = array_values( $value );
			} 
			else {
				$picker_value = esc_attr( $value );
			}
			
			$format    = empty( $this->dateFormat ) ? 'mdy' : esc_attr( $this->dateFormat );
			
			$picker_value = esc_attr( GFCommon::date_display( $picker_value, $format ) );
			$icon_class   = 'datepicker_with_icon gdatepicker_with_icon';
			$icon_url     = empty( $this->calendarIconUrl ) ? GFCommon::get_base_url() . '/images/datepicker/datepicker.svg' : $this->calendarIconUrl;
			$icon_url     = esc_url( $icon_url );

			$describedby_attribute  = $this->get_aria_describedby( array( "{$field_id}_date_format" ) );			
			
			$date_picker_placeholder = $this->get_field_placeholder_attribute();
			$date_format_sr_text = $this->get_date_format( 'screen_reader_text' );
			
			return "<div class='ginput_container ginput_container_date gse_date'>
					<input name='input_{$id}' id='{$field_id}' type='text' value='{$picker_value}' class='datepicker {$format} {$icon_class}' {$tabindex} {$disabled_text} {$date_picker_placeholder} {$describedby_attribute} {$invalid_attribute} {$required_attribute}/>
					<span id='{$field_id}_date_format' class='screen-reader-text'>{$date_format_sr_text}</span>
				</div>
				<input type='hidden' id='gforms_calendar_icon_$field_id' class='gform_hidden' value='$icon_url'/>";
		}
		else {
			return sprintf( "<div class='ginput_container ginput_container_select'><select name='input_%d' id='%s' class='%s' $tabindex %s %s %s>%s</select></div>", $id, $field_id, $css_class, $disabled_text, $required_attribute, $invalid_attribute, $this->get_choices( $value ) );
		}
		
	}
	
	public function get_field_placeholder_attribute() {
		
		if (  empty( $this->placeholder ) ) {
			$format = $this->is_form_editor() ? wp_strip_all_tags( $this->get_date_format() ) : $this->get_date_format();

			return sprintf( "placeholder='%s'", esc_attr( $format ) );
		}

		return parent::get_field_placeholder_attribute();
	}
	
	private function get_date_format( $type = 'label' ) {
		$format = empty( $this->dateFormat ) ? 'mdy' : $this->dateFormat;

		switch ( $format ) {
			case 'mdy':
				if ( $type === 'label' ) {
					$format = esc_attr__( 'mm/dd/yyyy', 'gravityforms' );
				} else {
					$format = esc_attr__( 'MM slash DD slash YYYY', 'gravityforms' );
				}
				break;
			case 'dmy':
				if ( $type === 'label' ) {
					$format = esc_attr__( 'dd/mm/yyyy', 'gravityforms' );
				} else {
					$format = esc_attr__( 'DD slash MM slash YYYY', 'gravityforms' );
				}
				break;
			case 'dmy_dash':
				if ( $type === 'label' ) {
					$format = esc_attr__( 'dd-mm-yyyy', 'gravityforms' );
				} else {
					$format = esc_attr__( 'DD dash MM dash YYYY', 'gravityforms' );
				}
				break;
			case 'dmy_dot':
				if ( $type === 'label' ) {
					$format = esc_attr__( 'dd.mm.yyyy', 'gravityforms' );
				} else {
					$format = esc_attr__( 'DD dot MM dot YYYY', 'gravityforms' );
				}
				break;
			case 'ymd_slash':
				if ( $type === 'label' ) {
					$format = esc_attr__( 'yyyy/mm/dd', 'gravityforms' );
				} else {
					$format = esc_attr__( 'YYYY slash MM slash DD', 'gravityforms' );
				}
				break;
			case 'ymd_dash':
				if ( $type === 'label' ) {
					$format = esc_attr__( 'yyyy-mm-dd', 'gravityforms' );
				} else {
					$format = esc_attr__( 'YYYY dash MM dash DD', 'gravityforms' );
				}
				break;
			case 'ymd_dot':
				if ( $type === 'label' ) {
					$format = esc_attr__( 'yyyy.mm.dd', 'gravityforms' );
				} else {
					$format = esc_attr__( 'YYYY dot MM dot DD', 'gravityforms' );
				}
				break;
		}

		return $this->is_form_editor() ? '<span>' . $format . '</span>' : $format;
	}
	
	public function get_choices( $value ) {
		$field = $this;
		
		$choices = '';
		$support_placeholders = false;
	
		
		if ( rgget('view') == 'entry' && empty( $value ) && rgblank( $field->placeholder ) ) {
			$choices .= "<option value=''></option>";
		}

		if ( is_array( $field->choices ) ) {

			if ( $support_placeholders && ! rgblank( $field->placeholder ) ) {
				$selected = empty( $value ) ? "selected='selected'" : '';
				$choices .= sprintf( "<option value='' %s class='gf_placeholder'>%s</option>", $selected, esc_html( $field->placeholder ) );
			}

			foreach ( $field->choices as $choice ) {
				
				//needed for users upgrading from 1.0
				$field_value = ! empty( $choice['value'] ) || $field->enableChoiceValue || $field->type == 'post_category' ? $choice['value'] : $choice['text'];
				if ( $field->enablePrice ) { 
					$price = rgempty( 'price', $choice ) ? 0 : GFCommon::to_number( rgar( $choice, 'price' ) );
					$field_value .= '|' . $price;
				}

				if ( ! isset( $_GET['gf_token'] ) && empty( $_POST ) && GFCommon::is_empty_array( $value ) && rgget('view') != 'entry' ) {
					$selected = rgar( $choice, 'isSelected' ) ? "selected='selected'" : '';
				} 
				else { 
					if ( is_array( $value ) ) {
						$is_match = false;
						foreach ( $value as $item ) {
							if ( RGFormsModel::choice_value_match( $field, $choice, $item ) ) {
								$is_match = true;
								break;
							}
						}
						$selected = $is_match ? "selected='selected'" : '';
					} 
					else { 
						$selected = RGFormsModel::choice_value_match( $field, $choice, $value ) ? "selected='selected'" : '';
					}
				}
				
				$dateFormat = $field->dateFormat;
				$choice_text = GFCommon::date_display( $choice['text'], $dateFormat );
				
				$choice_markup = sprintf( "<option value='%s' %s>%s</option>", esc_attr( $choice['text'] ), $selected, esc_html( $choice_text ) );
				
				$choices .= gf_apply_filters( array(
					'gform_field_choice_markup_pre_render',
					$field->formId,
					$field->id
				), $choice_markup, $choice, $field, $value );

			}
		}

		return $choices;
	}
	
	public function get_radio_choices( $value = '', $disabled_text = '', $form_id = 0 ) {
		$choices = '';

		if ( is_array( $this->choices ) ) {
			$is_entry_detail    = $this->is_entry_detail();
			$is_form_editor     = $this->is_form_editor();
			$is_admin           = $is_entry_detail || $is_form_editor;

			$field_choices      = $this->choices;
			$needs_other_choice = $this->enableOtherChoice;
			$editor_limited     = false;

			$choice_id = 0;
			$count     = 1;

			$tag = GFCommon::is_legacy_markup_enabled( $form_id ) ? 'li' : 'div';

			foreach ( $field_choices as $choice ) {
				if ( rgar( $choice, 'isOtherChoice' ) ) {
					if ( ! $needs_other_choice ) {
						continue;
					}
					$needs_other_choice = false;
				}

				$choices .= $this->get_choice_html( $choice, $choice_id, $value, $disabled_text, $is_admin );

				if ( $is_form_editor && $count >= 5 ) {
					$editor_limited = true;
					break;
				}

				$count ++;
			}

			if ( $needs_other_choice ) {
				$other_choice    = array(
					'text'          => GFCommon::get_other_choice_value( $this ),
					'value'         => 'gf_other_choice',
					'isSelected'    => false,
					'isOtherChoice' => true,
				);
				$field_choices[] = $other_choice;

				if ( ! $is_form_editor || ! $editor_limited ) {
					$choices .= $this->get_choice_html( $other_choice, $choice_id, $value, $disabled_text, $is_admin );
					$count ++;
				}
			}

			$total = sizeof( $field_choices );
			if ( $is_form_editor && ( $count < $total ) ) {
				$choices .= "<{$tag} class='gchoice_total'>" . sprintf( esc_html__( '%d of %d items shown. Edit field to view all', 'gravityforms' ), $count, $total ) . "</{$tag}>";
			}
		}

		return gf_apply_filters( array( 'gform_field_choices', $this->formId ), $choices, $this );
	}
	
	public function get_choice_html( $choice, &$choice_id, $value, $disabled_text, $is_admin ) {
		$form_id = absint( $this->formId );

		if ( GFCommon::is_legacy_markup_enabled( $form_id ) ) {
			return $this->get_legacy_choice_html( $choice, $choice_id, $value, $disabled_text, $is_admin );
		}

		if ( $is_admin || $form_id == 0 ) {
			$id = $this->id . '_' . $choice_id ++;
		} else {
			$id = $form_id . '_' . $this->id . '_' . $choice_id ++;
		}

		$field_value = ! empty( $choice['value'] ) || $this->enableChoiceValue ? $choice['value'] : $choice['text'];

		if ( $this->enablePrice ) {
			$price       = rgempty( 'price', $choice ) ? 0 : GFCommon::to_number( rgar( $choice, 'price' ) );
			$field_value .= '|' . $price;
		}

		if ( rgblank( $value ) && rgget( 'view' ) != 'entry' ) {
			$checked = rgar( $choice, 'isSelected' ) ? "checked='checked'" : '';
		} else {
			$checked = GFFormsModel::choice_value_match( $this, $choice, $value ) ? "checked='checked'" : '';
		}

		$aria_describedby = '';

		$tabindex = $this->get_tabindex();
		$dateFormat = $field->dateFormat;
		$choice_text = GFCommon::date_display( $choice['text'], $dateFormat );
		$label    = sprintf( "<label for='choice_%s' id='label_%s'>%s</label>", $id, $id, $choice_text );

		// Handle 'other' choice.
		if ( $this->enableOtherChoice && rgar( $choice, 'isOtherChoice' ) ) {
			$input_disabled_text = $disabled_text;

			if ( $value == 'gf_other_choice' && rgpost( "input_{$this->id}_other" ) ) {
				$other_value = rgpost( "input_{$this->id}_other" );
			} elseif ( ! empty( $value ) && ! GFFormsModel::choices_value_match( $this, $this->choices, $value ) ) {
				$other_value = $value;
				$value       = 'gf_other_choice';
				$checked     = "checked='checked'";
			} else {
				if ( ! $input_disabled_text ) {
					$input_disabled_text = "disabled='disabled'";
				}
				$other_value = empty( $choice['text'] ) ? GFCommon::get_other_choice_value( $this ) : $choice['text'];
			}

			$label .= "<br /><input id='input_{$this->formId}_{$this->id}_other' name='input_{$this->id}_other' type='text' value='" . esc_attr( $other_value ) . "' aria-label='" . esc_attr__( 'Other Choice, please specify', 'gravityforms' ) . "' $tabindex $input_disabled_text />";
		}

		$choice_markup = sprintf( "
			<div class='gchoice gchoice_$id'>
					<input class='gfield-choice-input' name='input_%d' type='radio' value='%s' %s id='choice_%s' onchange='gformToggleRadioOther( this )' %s $tabindex %s />
					%s
			</div>",
			$this->id, esc_attr( $field_value ), $checked, $id, $aria_describedby, $disabled_text, $label
		);

		/**
		 * Allows the HTML for a specific choice to be overridden.
		 *
		 * @since 1.9.6
		 * @since 1.9.12 Added the field specific version.
		 * @since 2.4.17 Moved from GF_Field_Radio::get_radio_choices().
		 *
		 * @param string         $choice_markup The choice HTML.
		 * @param array          $choice        The choice properties.
		 * @param GF_Field_Radio $field         The current field object.
		 * @param string         $value         The current field value.
		 */
		return gf_apply_filters( array( 'gform_field_choice_markup_pre_render', $this->formId, $this->id ), $choice_markup, $choice, $this, $value );
	}
	
	public function get_legacy_choice_html( $choice, &$choice_id, $value, $disabled_text, $is_admin ) {
		$form_id = absint( $this->formId );

		if ( $is_admin || $form_id == 0 ) {
			$id = $this->id . '_' . $choice_id ++;
		} else {
			$id = $form_id . '_' . $this->id . '_' . $choice_id ++;
		}

		$field_value = ! empty( $choice['value'] ) || $this->enableChoiceValue ? $choice['value'] : $choice['text'];

		if ( $this->enablePrice ) {
			$price       = rgempty( 'price', $choice ) ? 0 : GFCommon::to_number( rgar( $choice, 'price' ) );
			$field_value .= '|' . $price;
		}

		if ( rgblank( $value ) && rgget( 'view' ) != 'entry' ) {
			$checked = rgar( $choice, 'isSelected' ) ? "checked='checked'" : '';
		} else {
			$checked = GFFormsModel::choice_value_match( $this, $choice, $value ) ? "checked='checked'" : '';
		}

		$tabindex    = $this->get_tabindex();
		$dateFormat = $field->dateFormat;
		$choice_text = GFCommon::date_display( $choice['text'], $dateFormat );
		$label       = sprintf( "<label for='choice_%s' id='label_%s'>%s</label>", $id, $id, $choice_text );
		$input_focus = '';

		// Handle 'other' choice.
		if ( $this->enableOtherChoice && rgar( $choice, 'isOtherChoice' ) ) {
			$other_default_value = empty( $choice['text'] ) ? GFCommon::get_other_choice_value( $this ) : $choice['text'];

			$onfocus = ! $is_admin ? 'jQuery(this).prev("input")[0].click(); if(jQuery(this).val() == "' . $other_default_value . '") { jQuery(this).val(""); }' : '';
			$onblur  = ! $is_admin ? 'if(jQuery(this).val().replace(" ", "") == "") { jQuery(this).val("' . $other_default_value . '"); }' : '';

			$input_focus  = ! $is_admin ? "onfocus=\"jQuery(this).next('input').focus();\"" : '';
			$value_exists = GFFormsModel::choices_value_match( $this, $this->choices, $value );

			if ( $value == 'gf_other_choice' && rgpost( "input_{$this->id}_other" ) ) {
				$other_value = rgpost( "input_{$this->id}_other" );
			} elseif ( ! $value_exists && ! empty( $value ) ) {
				$other_value = $value;
				$value       = 'gf_other_choice';
				$checked     = "checked='checked'";
			} else {
				$other_value = $other_default_value;
			}

			$label = "<input class='small' id='input_{$this->formId}_{$this->id}_other' name='input_{$this->id}_other' type='text' value='" . esc_attr( $other_value ) . "' aria-label='" . esc_attr__( 'Other', 'gravityforms' ) . "' onfocus='$onfocus' onblur='$onblur' $tabindex $disabled_text />";
		}

		$choice_markup = sprintf( "
			<li class='gchoice gchoice_$id'>
				<input name='input_%d' type='radio' value='%s' %s id='choice_%s' $tabindex %s %s />
				%s
			</li>",
			$this->id, esc_attr( $field_value ), $checked, $id, $disabled_text, $input_focus, $label
		);

		/**
		 * Allows the HTML for a specific choice to be overridden.
		 *
		 * @since 1.9.6
		 * @since 1.9.12 Added the field specific version.
		 * @since 2.4.17 Moved from GF_Field_Radio::get_radio_choices().
		 *
		 * @param string         $choice_markup The choice HTML.
		 * @param array          $choice        The choice properties.
		 * @param GF_Field_Radio $field         The current field object.
		 * @param string         $value         The current field value.
		 */
		return gf_apply_filters( array( 'gform_field_choice_markup_pre_render', $this->formId, $this->id ), $choice_markup, $choice, $this, $value );
	}
	
	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
		$return = esc_html( $value );
		return GFCommon::selection_display( $return, $this, $entry['currency'] );
	}
	
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		$return = esc_html( $value );
		return GFCommon::selection_display( $return, $this, $currency, $use_text );
	}
	
	public function sanitize_entry_value( $value, $form_id ) {

		$value = wp_strip_all_tags( $value );

		return $value;
	}
	
	public function get_filter_operators() {
		$operators = $this->type == 'product' ? array( 'is' ) : array( 'is', 'isnot', '>', '<' );

		return $operators;
	}
	
}

GF_Fields::register( new GSE_Field() );
