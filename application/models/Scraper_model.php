<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// REFERENCE: http://simplehtmldom.sourceforge.net

/* TO DO:

1) - other things than straight up returns, logic for it too. I think all it needs is the attr there. sick.
2) - Looping rules
3) - additional functions (mainly string functions)
4) - If functions?
5) - variable name functions

// TRAVERSE OPTIONS
'parent','first_child','last_child','next_sibling','prev_sibling'

*/

class Scraper_model extends CI_Model {

	public $values = [];
	public $count = [];
	
	public function __construct(){
		parent::__construct();
		$this->load->helper('scraper/simple_html_dom');
		$this->load->helper('external_url');
	}
	
	public function scrape_site($url, $rules = [], $param = []){
		$rs = isValidUrl($url);
		if(!$rs['success']){
			return $rs;			
		}
		
		$html = file_get_html($url);
		// return : directly return a function call. can be:
		/*
		class,innertxt,outertxt,src,href,
		
		*/
		/*[
			'name' => 'test',
			'selector' => 'a.btn'
			'attr' => 'class';		
		];
		*/
		$this->values = [];
		$this->count = [];
		foreach($rules as $rule){
			$this->run_rule($rule,$html);
		}
		/*
		$classes = [];
		// Find all links 
		foreach($html->find('a') as $element) 
			   $classes[] = $element->class;
			   */
		return rs_success("Here we go: $url",['test'=>true, 'values' => $this->values]);
	}
	
	function run_rule($rule,$html){
	$instances = NULL;
		if(!isset($rule['instance']))
			$instances = $html->find($rule['selector']);
		else{
			$instances = $html->find($rule['selector'],$rule['instance']);
			if(is_object($instances))
				$instances = [$instances];
		}
		
		if(is_array($instances) && count($instances)){
			foreach($instances as $instance){
				if(isset($rule['traverse'])){
					$instance = $instance->$rule['traverse']();
					if(!is_object($instance))
						continue;
				}
				if(isset($rule['rules'])){
					foreach($rule['rules'] as $r){
						$this->run_rule($r,$instance);
					}
				}
				elseif(isset($rule['attr']) && isset($instance->$rule['attr'])){
					$value = $instance->$rule['attr'];
					$name = NULL;
					if(is_array($rule['name'])){
						$name_instance = $instance;
						if(isset($rule['name']['selector']) && is_object($name_instance))
							$name_instance = $name_instance->find($rule['name']['selector']);
						if(isset($rule['name']['traverse']) && is_object($name_instance))
							$name_instance = $name_instance->$rule['name']['traverse']();
						if(is_object($name_instance))
							$name = $name_instance->$rule['name']['attr'];
					}
					else
						$name = $rule['name'];
					if(!isset($this->count[$name]))
						$this->count[$name] = 0;
					$this->values[] = [
						'name' =>$name,
						'instance' => $this->count[$name],
						'value' => $value
					];
					$this->count[$name] ++;
				}		
			}
		}
	}
}