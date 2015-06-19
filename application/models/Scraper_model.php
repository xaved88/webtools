<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// REFERENCE: http://simplehtmldom.sourceforge.net

/* TO DO:

DONE - 1) - other things than straight up returns, logic for it too. I think all it needs is the attr there. sick.
DONE - 2) - Looping rules
DONE - 3) - additional functions (mainly string functions)
DONE - 4) - variable name functions
DONE - 5) Multi Traverse/select.
6) Auto go to other links... like for a search page, just go get them all.
7) Allow links to files, not just URLs...

// ATTR OPTIONS
id,class,innertxt,outertxt,src,href,

// TRAVERSE OPTIONS
'parent','first_child','last_child','next_sibling','prev_sibling'

// STRING FUNCTIONS
'trim(all,left(str),right(str)','append','prepend','replace(i)'
'lowercase(first)','uppercase(all,first,words)','length','count(str)'

*/

class Scraper_model extends CI_Model {
	public $return_values = [];
	public $values = [];
	public $count = [];
	
	public function __construct(){
		parent::__construct();
		$this->load->helper('scraper/simple_html_dom');
		$this->load->helper('external_url');
	}
	
	public function scrape_sites($urls, $rules = [], $param = []){
		if(!is_array($urls) || !count($urls))
			return rs_error("Error: Bad Data...");
		
		$error = [];
		$success = [];

		foreach($urls as $url){
			$this->values = NULL;
			$this->count = NULL;
			$this->values = [];
			$this->count = [];
			$rs = $this->scrape_site($url,$rules,$param);
			
			if($rs['success']){
				$this->return_values[] = $this->values;
				$success[] = $url;
			}
			else{
				$error[] = $url;
			}
		}
		
		$message = "";
		if(!count($error))
			$message = "Complete success. All URLs scraped";
		if(count($success)){
			if(!$message)
				$message = "Success. Some sites scraped unsuccessfully.";
			return rs_success($message,['success'=>$success,'error'=>$error,'values'=>$this->return_values]);
		}
		
		return rs_error("Error: could not scrape any sites",['success'=>$success,'error'=>$error,'values'=>$this->return_values]);
		
	}
	public function scrape_site($url, $rules = [], $param = []){
		$rs = isValidUrl($url);
		if(!$rs['success']){
			return $rs;			
		}
		
		$html = file_get_html($url);
		
		$this->values = [];
		$this->count = [];
		foreach($rules as $rule){
			$this->run_rule($rule,$html);
		}
		
		return rs_success("Site Successfully scraped!",['values'=>$this->values]);
	}
	
	function run_rule($rule,$html){
		$instances = $this->get_rule_instances($rule,$html);
		if(is_array($instances) && count($instances)){
			foreach($instances as $instance){
				if(isset($rule['rules'])){
					foreach($rule['rules'] as $r){
						$this->run_rule($r,$instance);
					}
				}
				elseif(isset($rule['attr']) && isset($instance->$rule['attr'])){
					$name = NULL;
					if(is_array($rule['name'])){
						$name_instance = $this->get_rule_instances($rule['name'],$instance);
						if(is_array($name_instance))
							$name_instance = $name_instance[0];
						if(is_object($name_instance))
							$name = $this->get_rule_value($rule['name'],$name_instance);
					
						/*
						$name_instance = $instance;
						if(isset($rule['name']['selector']) && is_object($name_instance))
							$name_instance = $name_instance->find($rule['name']['selector']);
						if(isset($rule['name']['traverse']) && is_object($name_instance))
							$name_instance = $name_instance->$rule['name']['traverse']();
						if(is_object($name_instance)){
							$name = $name_instance->$rule['name']['attr'];
							if(isset($rule['name']['functions']))
								$this->rule_string_functions($rule['name'],$name);
						}
						*/
					}
					if(!$name && isset($rule['name']))
						$name = $rule['name'];
					if(!$name)
						$name = "UNDEFINED";
					
					if(!isset($this->count[$name]))
						$this->count[$name] = 0;
					
					$value = $this->get_rule_value($rule,$instance);
					
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
	function get_rule_instances($rule,$html){
		$instances = [$html];
		if(isset($rule['dom']) && count($rule['dom'])){
			$dom_count = count($rule['dom']);
			foreach($rule['dom'] as $type=>$action){
				$temp = NULL;
				$temp = [];
				if( is_array($instances) && count($instances) ){
					if(in_array($type,['selector','traverse']))
					foreach($instances as $i=>$instance){
						if(is_object($instance)){
							switch($type){
								case 'selector':
									$a = $instance->find($action);
									if(is_array($a) && count($a))
									foreach($a as $b){
										$temp[] = $b;
									}
									else if(is_object($a))
										$temp[] = $a;
									break;
								case 'traverse':
									$a = $instance->$action();
									if(is_array($a) && count($a))
									foreach($a as $b){
										$temp[] = $b;
									}
									else if(is_object($a))
										$temp[] = $a;
									break;
							}
						}
					}
					else if($type=='instance'){						
						if(isset($instances[$action]))
							$temp[] = $instances[$action];
						else
							$temp[] = end($instances);
					}
				}
				$instances = NULL;
				$instances = $temp;
			}
		}
		return $instances;
	}
	function get_rule_value($rule,$html){
		$value = $html->$rule['attr'];
		$this->rule_string_functions($rule,$value);
		return $value;
	}
	function rule_string_functions($rule,&$value){
		if(isset($rule['functions']) && is_array($rule['functions']) && count($rule['functions']))
		foreach($rule['functions'] as $f){
			switch($f['type']){
				case 'trim':
					if(isset($f['param'][0]) && $f['param'][0] == "left" && isset($f['param'][1])){
						$value = @explode($f['param'][1],$value)[0];
					}
					elseif(isset($f['param'][0]) && $f['param'][0] == "right" && isset($f['param'][1])){
						$value = @end(explode($f['param'][1],$value));
					}
					else{
						$value = trim($value);
					}
					break;
				case 'append':
					if(isset($f['param'][0]))
						$value .= $f['param'][0];
					break;
				case 'prepend':
					if(isset($f['param'][0]))
						$value = $f['param'][0] . $value;
					break;
				case 'replace':
					if(isset($f['param'][0]) && isset($f['param'][1])){
						if(isset($f['param'][2]) && $f['param'][2])
							$value = str_ireplace($f['param'][0],$f['param'][1],$value);
						else
							$value = str_replace($f['param'][0],$f['param'][1],$value);
					}
					break;
				case 'lowercase':
					if(isset($f['param'][0]) && $f['param'][0] == 'first')
						$value = lcfirst($value);
					else
						$value = strtolower($value);
					break;
				case 'uppercase':
					if(isset($f['param'][0]) && $f['param'][0] == 'first')
						$value = ucfirst($value);
					elseif(isset($f['param'][0]) && $f['param'][0] == 'worlds')
						$value = ucwords($value);
					else
						$value = strtoupper($value);
					break;
				case 'length':
					$value = strlen($value);
					break;
				case 'count':
					if(isset($f['param'][0]))
						$value = substr_count($value,$f['param'][0]);
					break;
			}
			
		}
	}
}