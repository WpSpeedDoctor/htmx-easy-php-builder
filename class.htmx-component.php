<?php


/**
 * Adds HTMX attributes into HTML tag replacing attribute `data-injection`.
 * In any tag that has `data-injection` will be added HTMX attributes.
 * Contains error handling and displaying warning messages if request failed or timed out.
 * 
 * Usage:
 * Instantiate object with arguments such as 'html' containing the markup with 'data-injection' attribute.
 * 
 * $htmx = new Htmx_Component([
 *   'html' => '<button data-injection>Click me</button>',
 *   'htmx-attributes' => [
 *     'hx-target' => '#result-div',
 *     'hx-swap' => 'innerHTML'
 *   ]
 * ]);
 * 
 * // Optional: Add a target element before or after the component
 * $htmx->add_target_before(); // or add_target_after();
 * 
 * // Get the final HTML with HTMX attributes injected
 * $output = $htmx->get_output();
 * 
 * Arguments:
 * 'html'           - (string) Required. HTML markup containing 'data-injection' attribute
 * 'get'            - (bool) true - Makes GET request, default POST
 * 'query_string'   - (string) The query string of request, default no query string
 * 'values'         - (array) Additional parameters that will be passed to POST request or GET query string
 * 'htmx-attributes'- (array) Any HTMX attributes example: ['hx-target'=> '#form-results','hx-swap'=> 'innerHTML']
 * 
 * Properties:
 * $request_url     - (string) URL for AJAX requests, defaults to admin-ajax.php
 * $indicator_url   - (string) URL for the loading indicator image
 * $is_indicator_displayed - (bool) Whether to show a loading indicator, default true
 * $indicator_markup- (string) HTML for the loading indicator
 * $inputs_markup   - (string) Additional HTML inputs to include
 * $error_message   - (string) Custom error message shown when request fails
 * $timeout_message - (string) Custom message shown when request times out
 * 
 * Methods:
 * __construct()    - Initializes the component with configuration
 * add_target_before() - Adds target container before the component
 * add_target_after() - Adds target container after the component
 * get_output()     - Returns the final HTML with all HTMX attributes injected
 * load_htmx_script()	- Adds script to load the HTMX JS library
 * 
 * Private methods:
 * add_indicator()  - Adds loading indicator to the markup
 * add_target_markup() - Adds target container element to the markup
 * get_htmx_attrs() - Generates the complete HTMX attributes string
 * get_htmx_vals()  - Generates the hx-vals attribute with JSON-encoded values
 * get_request_type_htmx_markup() - Generates hx-get or hx-post attribute
 * get_json_vals()  - Properly encodes values for HTML output
 * add_error_js_markup() - Adds JavaScript for error and timeout handling
 */

class Htmx_Component{

	public	string $request_url;

	public	string $indicator_url;

	public	bool $is_indicator_displayed = true;
	
	public	string $indicator_markup;

	public	string $inputs_markup = '';
	
	private	array $args;

	private	string $target=''; 

	private	string $error_message='';

	private	string $timeout_message='';

	private	string $htmx_load_markup='';

	public function __construct($args){

		if( empty($args['html']) || !str_contains( $args['html'], 'data-injection') ){

			echo 'HTML input without data-injection placeholder';

			return;
		}

		$this->args = $args;

		$this->request_url = admin_url( 'admin-ajax.php' );
        
		$this->indicator_url = admin_url( 'images/spinner.gif' );

		if( $this->is_indicator_displayed ){

			$this->indicator_markup = '<img class="htmx-indicator" id="spinner" src="'.$this->indicator_url.'" height="15" width="15">';
	
		}
	}

	public function add_target_before(){

		$this->target = 'before';
	}

	public function add_target_after(){

        $this->target = 'after';
    }

	public function get_output(){
		
		if( empty($this->request_url) ){
			return '';
		}

		$html_attrs = $this->get_htmx_attrs();
		
		$output = '';

		$output .= $this->htmx_load_markup;

		$output .= str_replace( 'data-injection' , $html_attrs, $this->args['html'] );
				
		$this->add_target_markup($output);
		
		
		$this->add_indicator( $output );

		$this->add_error_js_markup($output);
		
		return $output;
	}

	private function add_indicator( &$result){

		if( !$this->is_indicator_displayed || empty( $this->indicator_markup) ){
			
			return;
		}

		if( !str_contains( $result,'<form' ) ){

			$result .= $this->indicator_markup;
		}


		$result = str_replace( '</form>', "{$this->indicator_markup}\n</form>", $result );

	}
	
    private function add_target_markup( &$output ){

		if( empty( $this->args['htmx-attributes']['hx-target'] ) ){
			return;
		}

		$target_id = str_replace('#', '', $this->args['htmx-attributes']['hx-target']);

		$target_template = <<<HTML
		<span id="{$target_id}"></span>
		HTML;

		switch($this->target){
			
            case 'before':
				$output = "{$target_template}\n{$output}";
				break;

			case 'after':
				$output .= "{$target_template}";
			break;
			
		}
	}

	private function get_htmx_attrs(){

		$htmx_attrs = '';

		$htmx_attrs .= $this->get_request_type_htmx_markup();
		
		$htmx_attrs.= $this->get_htmx_vals();

		foreach ($this->args['htmx-attributes'] as $key => $value){

			$htmx_attrs.= <<<HTML
			{$key}="{$value}"

			HTML;
		}

		return $htmx_attrs;
	}

	private function get_htmx_vals(){
		
		if(	empty( $this->args['values'] ) || !is_array(  $this->args['values'] ) ){
			return '';
		}

		$htmx_vals = $this->get_json_vals( $this->args['values'] );

		return <<<HTML
		hx-vals='{$htmx_vals}'

		HTML;
	}

	private function get_request_type_htmx_markup(){

		$request_type = $this->args['get']??false ? "hx-get" : "hx-post";

		$query_string = empty($this->args['query_string']) ? '' : '?'.$this->args['query_string'];

		return $request_type.'="'.$this->request_url.$query_string . '"'.PHP_EOL;
	}

	/**
	 * this ensures correcty formatting for HTML ouput
	 * @param array $htmx_values
	 * @return string
	 */
	private function get_json_vals( $htmx_values ){

		return json_encode($htmx_values, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}

	private function add_error_js_markup( &$result ){

		if( defined('HTMX-ERROR-JS') ){

			return;
		}

		define( 'HTMX-ERROR-JS', true );

		$error_message = empty( $this->error_message ) ? 'An error occurred.' : $this->error_message;

		$timeout_message = empty( $this->timeout_message ) ? 'Request timed out.' : $this->timeout_message;

		$result .= <<<HTML
		<script>
			function show_htmx_warning(event, message){
				const sourceEl = event.detail.elt;

				const targetSelector = sourceEl.getAttribute('hx-target');

				if(!targetSelector) return;

				const target = document.querySelector(targetSelector);

				if(!target) return;

				target.innerHTML = `<div class="alert alert-warning">\${message}</div>`;
			}

			document.body.addEventListener('htmx:error', function(event){
				show_htmx_warning(event, '{$error_message}');
			});

			document.body.addEventListener('htmx:timeout', function(event){
				show_htmx_warning(event, '{$timeout_message}');
			});
		</script>
		HTML;
	}

	public function load_htmx_script(){

		$this->htmx_load_markup = '<script src="https://unpkg.com/htmx.org@2.0.4" defer></script>'; 
	}
}