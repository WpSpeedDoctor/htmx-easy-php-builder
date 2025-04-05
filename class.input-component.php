<?php

/**
 * Input_Components - A utility class for generating HTML form elements with HTMX support.
 * 
 * This class provides methods to create various HTML form inputs, select dropdowns,
 * textareas, buttons, and more with support for HTMX integration through a 'data-injection'
 * attribute placeholder. It can output components individually or organize them in tables or forms.
 * 
 * Usage:
 * $input = new Input_Components();
 * 
 * // Enable HTMX injection in components
 * $input->set_injection();
 * 
 * // Add various form elements
 * $input->add_input_text([
 *   'name' => 'username',
 *   'id' => 'user-name',
 *   'class' => 'form-control',
 *   'placeholder' => 'Enter username'
 * ]);
 * 
 * $input->add_select([
 *   'name' => 'country',
 *   'options' => ['us' => 'United States', 'ca' => 'Canada']
 * ]);
 * 
 * // Get HTML output as a form
 * $form = $input->get_form([
 *   'id' => 'registration',
 *   'class' => 'htmx-form',
 *   'submit-text' => 'Register',
 *   'table' => true
 * ]);
 * 
 * Properties:
 * $components     - (array) Collection of form components
 * $injection      - (string) HTMX injection attribute placeholder
 * $appendix       - (string) HTML to append to the output
 * $prefix         - (string) HTML to prepend to the output
 * 
 * Public Methods:
 * set_injection()      - Enables HTMX data-injection attribute in components
 * add_input_*()        - Methods for various input types (text, email, number, etc.)
 * add_textarea()       - Adds a textarea component
 * add_select()         - Adds a select dropdown component
 * add_button()         - Adds a button component
 * add_checkbox()       - Adds a checkbox input component
 * add_radio()          - Adds a radio input component
 * add_hidden()         - Adds a hidden input component
 * add_empty()          - Adds an empty row (for table layouts)
 * add_subheader()      - Adds a subheader row (for table layouts)
 * append_html()        - Appends custom HTML to output
 * prepend_html()       - Prepends custom HTML to output
 * get_table()          - Returns HTML table with all components
 * get_html()           - Returns HTML of all components
 * get_form()           - Returns complete HTML form with all components
 * 
 * Common Arguments Format for Input Methods:
 * 'name'           - (string) Required. Input name attribute
 * 'id'             - (string) Optional. Input ID attribute
 * 'value'          - (string) Optional. Input value attribute
 * 'class'          - (string) Optional. CSS classes
 * 'placeholder'    - (string) Optional. Placeholder text
 * 'attributes'     - (array) Optional. Additional HTML attributes as key-value pairs
 * 'column_before'  - (string) Optional. HTML content before the input in table layout
 * 'column_after'   - (string) Optional. HTML content after the input in table layout
 * 
 * For select inputs:
 * 'options'        - (array) Required. Key-value pairs for option values and labels
 * 'selected'       - (string) Optional. Key of the selected option
 * 
 * For button inputs:
 * 'text'           - (string) Optional. Button text content
 * 'type'           - (string) Optional. Button type (submit, button, reset)
 * 
 * For get_table() method:
 * 'id'             - (string) Optional. Table ID attribute
 * 'class'          - (string) Optional. Table CSS classes
 * 'td_class'       - (string) Optional. CSS class for all table cells
 * 
 * For get_form() method:
 * 'id'             - (string) Optional. Form ID attribute
 * 'class'          - (string) Optional. Form CSS classes
 * 'table'          - (bool) Optional. Whether to render inputs in a table layout
 * 'submit-text'    - (string) Required. Text for the submit button
 */

class Input_Components {

	public array $components = [];

	private string $injection = '';

	private string $appendix = '';

	private string $prefix = '';
	
	public function set_injection(){
	
		$this->injection = "\n data-injection";

	}

	public function add_input_text( $args ){
		$this->_add_input( 'text', $args );
	}

	public function add_input_email( $args ){
		$this->_add_input( 'email', $args );
	}

	public function add_input_number( $args ){
		$this->_add_input( 'number', $args );
	}

	public function add_input_password( $args ){
		$this->_add_input( 'password', $args );
	}

	public function add_input_tel( $args ){
		$this->_add_input( 'tel', $args );
	}

	public function add_input_url( $args ){
		$this->_add_input( 'url', $args );
	}

	public function add_input_search( $args ){
		$this->_add_input( 'search', $args );
	}

	public function add_input_date( $args ){
		$this->_add_input( 'date', $args );
	}

	public function add_input_time( $args ){
		$this->_add_input( 'time', $args );
	}

	public function add_input_datetime_local( $args ){
		$this->_add_input( 'datetime-local', $args );
	}

	public function add_input_color( $args ){
		$this->_add_input( 'color', $args );
	}

	public function add_input_range( $args ){
		$this->_add_input( 'range', $args );
	}

	public function add_checkbox( $args ){
		$this->_add_input( 'checkbox', $args );
	}

	public function add_radio( $args ){
		$this->_add_input( 'radio', $args );
	}

	public function add_hidden( $args ){
		$this->_add_input( 'hidden', $args );
	}

	public function add_input_reset( $args ){
		$this->_add_input( 'reset', $args );
	}

	public function add_input_button( $args ){
		$this->_add_input( 'button', $args );
	}

	private function _add_input( $type, $args ){
		$args['type'] = $type;

		$args['html'] = $this->render_input( $args );

		$this->components[] = $args;
	}

	public function add_textarea( $args ){

		if( empty( $args['name'] ) ){
			return;
		}

		$name = $this->_esc_attr( $args['name'] );

		$id_attr = $this->_add_id_attr( $args );

		$class = $this->_get_attr( $args, 'class' );

		$value = $this->_get_html( $args, 'value' );

		$placeholder_attr = $this->_add_placeholder_attr( $args );

		$attributes = $this->build_attributes( $this->_get_array( $args, 'attributes' ) );

		$html = <<<HTML
		<textarea name="{$name}"{$id_attr} class="{$class}"{$placeholder_attr}{$attributes}{$this->injection}>{$value}</textarea>

		HTML;

		$args['html'] = $html;

		$this->components[] = $args;

	}

	public function add_select( $args ){

		if( empty( $args['name'] ) || empty( $args['options'] ) || ! is_array( $args['options'] ) ){
			return;
		}

		$name = $this->_esc_attr( $args['name'] );

		$id_attr = $this->_add_id_attr( $args );

		$class = $this->_get_attr( $args, 'class' );

		$selected = $this->_get_attr( $args, 'selected' );

		$attributes = $this->build_attributes( $this->_get_array( $args, 'attributes' ) );

		$options_html = '';

		if( isset( $args['placeholder'] ) ){
			$placeholder = $this->_esc_html( $args['placeholder'] );
		
			$options_html .= <<<HTML
			<option value="">{$placeholder}</option>
			
			HTML;
		}

		foreach( $args['options'] as $key => $label ){
			$key = $this->_esc_attr( $key );
		
			$label = $this->_esc_html( $label );
		
			$select_attr = ( $selected === $key ) ? ' selected' : '';
		
			$options_html .= <<<HTML
				<option value="{$key}"{$select_attr}>{$label}</option>
				
				HTML;
		}

		$html = <<<HTML
			<select name="{$name}"{$id_attr} class="{$class}"{$attributes}{$this->injection}>
			{$options_html}</select>

			HTML;

		$args['html'] = $html;

		$this->components[] = $args;

	}

	public function add_button( $args ){

		if( empty( $args['name'] ) ){
			return;
		}

		$type = $this->_get_attr( $args, 'type', 'button' );

		$name = $this->_esc_attr( $args['name'] );

		$id_attr = $this->_add_id_attr( $args );

		$class = $this->_get_attr( $args, 'class' );

		$text = $this->_get_html( $args, 'text' );

		$attributes = $this->build_attributes( $this->_get_array( $args, 'attributes' ) );

		$html = <<<HTML
		<button type="{$type}" name="{$name}"{$id_attr} class="{$class}"{$attributes}{$this->injection}>{$text}</button>

		HTML;

		$args['html'] = $html;

		$this->components[] = $args;

	}

	public function add_empty(){
		$this->components[] = [ 'type' => 'empty' ];
	}

	public function add_subheader( $label ){
		$this->components[] = [ 'type' => 'subheader', 'label' => $label ];
	}

	public function append_html( $html ){
		
		$this->appendix .= $html;
	
	}

	public function prepend_html( $html ){
		
        $this->prefix = $html. $this->prefix;
    
    }

	/**
	 * Generates an HTML table with input rows based on the provided arguments.
	 *
	 * @param array $table_args -
	 * - id => string - Optional ID for the table. Defaults to 'inputs-table'.
	 * - class => string - Optional CSS class for the table.
	 * - td_class => string - Optional CSS class for the table data cells.
	 *
	 * @return string - The complete HTML table markup.
	 */

	public function get_table( $table_args ){

		$table_id = $this->_get_attr( $table_args, 'id', 'inputs-table' );

		$table_class = $this->_get_attr( $table_args, 'class' );

		$td_class = $this->add_space( $this->_get_attr( $table_args, 'td_class' ) );

		$rows = $this->build_table_rows( $table_id, $td_class );

		$html = <<<HTML
		<table id="{$table_id}" class="{$table_class}">
		{$rows}</table>

		HTML;

		return $this->prefix . $html. $this->appendix;;

	}

	private function build_table_rows( $table_id, $td_class ){

		$rows = '';

		foreach( $this->components as $component ){
			$rows .= $this->build_table_row( $component, $table_id, $td_class );
		}

		return $rows;

	}

	private function build_table_row( $component, $table_id, $td_class ){

		if( isset( $component['type'] ) && $component['type'] === 'empty' ){
			return <<<HTML
			<tr><td class="{$td_class}{$table_id}-col-empty" colspan="3">&nbsp;</td></tr>

			HTML;
		}

		if( isset( $component['type'] ) && $component['type'] === 'subheader' ){
			$label = $this->_get_html( $component, 'label' );
			return <<<HTML
			<tr><td class="{$td_class}{$table_id}-col-subheader" colspan="3"><strong>{$label}</strong></td></tr>

			HTML;
		}

		$before = $this->_get_html( $component, 'column_before' );
		$field = $component['html'];
		$after = $this->_get_html( $component, 'column_after' );

		$class1 = "{$td_class}{$table_id}-col-1";
		$class2 = "{$td_class}{$table_id}-col-2";
		$class3 = "{$td_class}{$table_id}-col-3";

		if( isset( $component['column_after'] ) ){
			return <<<HTML
			<tr>
				<td class="{$class1}">{$before}</td>
				<td class="{$class2}">{$field}</td>
				<td class="{$class3}">{$after}</td>
			</tr>

			HTML;
		}

		return <<<HTML
		<tr>
			<td class="{$class1}">{$before}</td>
			<td class="{$class2}">{$field}</td>
		</tr>

		HTML;

	}

	private function render_input( $args ){

		$input = $this->prepare_input_args( $args );

		if( ! $input ){
			return '';
		}

		$attributes = $this->build_attributes( $input['attributes'] );

		$id_attr = $this->_add_id_attr( $args );

		$placeholder_attr = $this->_add_placeholder_attr( $args );

		$html = <<<HTML
<input type="{$input['type']}" name="{$input['name']}"{$id_attr} value="{$input['value']}" class="{$input['class']}"{$placeholder_attr}{$attributes}{$this->injection}>

HTML;

		return $html;

	}

	private function prepare_input_args( $args ){

		if( empty( $args['name'] ) || empty( $args['type'] ) ){
			return false;
		}

		return [
			'type' => $this->_esc_attr( $args['type'] ),
			'name' => $this->_esc_attr( $args['name'] ),
			'id' => $this->_get_attr( $args, 'id' ),
			'value' => $this->_get_attr( $args, 'value' ),
			'class' => $this->_get_attr( $args, 'class' ),
			'placeholder' => $this->_get_attr( $args, 'placeholder' ),
			'attributes' => $this->_get_array( $args, 'attributes' )
		];

	}

	private function _add_attr_markup( $args, $attr ){

		$attr_value = $this->_get_attr( $args, $attr );

		if( empty( $attr_value ) ){
			
			return '';
		} 

		return <<<HTML
		 {$attr}="{$attr_value}"
		HTML;

	}

	private function _add_id_attr( $args ){

		$id = $this->_get_attr( $args, 'id' );
	
		return ($id === '') ? '' : ' id="' . $id . '"';
	}
	
	private function _add_placeholder_attr( $args ){
	
		$placeholder = $this->_get_attr( $args, 'placeholder' );
	
		return ($placeholder ==='') ? '' : ' placeholder="' .$placeholder . '"';
	}
	
	private function _get_attr( $args, $key, $default = '' ){
	
		if( !isset( $args[ $key ] ) || $args[ $key ] === '' ){
			return $default;
		}
	
		return $this->_esc_attr( $args[ $key ] );
	}
	
	private function _get_html( $args, $key, $default = '' ){
	
		if( empty( $args[ $key ] ) ){
			return $default;
		}
	
		return $this->_esc_html( $args[ $key ] );
	}
	
	private function _get_array( $args, $key ){
	
		if( !is_array( $args[ $key ]??false ) ){
			return [];
		}
	
		return $args[ $key ];
	}

	private function build_attributes( $attributes ){

		if( empty( $attributes ) ){
			return '';
		}

		$rendered = [];

		foreach( $attributes as $key => $val ){
			$key = $this->_esc_attr( $key );
			$val = $this->_esc_attr( $val );
			$rendered[] = $key . '="' . $val . '"';
		}

		$attrs = implode( ' ', $rendered );

		return $attrs ? ' ' . $attrs : '';

	}

	private function _esc_attr( $value ){

		if( is_callable( 'esc_attr' ) ){
			return esc_attr( $value );
		}

		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );

	}

	private function _esc_html( $value ){

		if( is_callable( 'esc_html' ) ){
			return esc_html( $value );
		}

		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );

	}

	public function get_html(){

		$html = implode( '', array_column( $this->components, 'html' ) );

		return $this->prefix . $html. $this->appendix;
	}

	/**
	 * Generates an HTML form based on the provided arguments.
	 *
	 * @param array $args -
	 * - class => string - Optional CSS class for the form.
	 * - id => string - Optional ID attribute for the form.
	 * - table => bool - Whether render intputs into table.
	 * - submit-text => string - The text for the submit button.
	 *
	 * @return string - The complete HTML form markup.
	 */

	public function get_form( $args ){

		$class_markup = $this->_add_attr_markup( $args, 'class' );

		$id_markup = $this->_add_attr_markup( $args, 'id' );

		$table_args = [
			'class' => 'form-table',
            'id' => $this->_get_attr( $args, 'id' ).'-table',
		];

		$form_content = empty( $args['table'] ) ?  $this->get_html() : $this->get_table( $table_args );
		
		$submit_text = $this->_esc_html( $args['submit-text'] );

		$form_attributes = "{$id_markup}{$class_markup}{$this->injection}";

		return <<<HTML
		<form {$form_attributes}>
			{$form_content}
			<input class="htmx-form-submit" type="submit" value="{$submit_text}">
		</form>
		HTML;
	}

	private function add_space($string){

		if( empty( $td_class ) ) {

			return $string;
		}
		
		return $string.' ';
	}
}