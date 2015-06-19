<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function rs_success($messages = [], $data = []){
	if(!is_array($messages))
		$messages = [$messages];
	if(!is_array($data))
		$data = [$data=>true];
	return ['success'=>true, 'error'=>false, 'messages'=>$messages, 'data'=>$data];
}
function _rs_success($messages = [], $data = []){
	return json_encode(rs_success($messages,$data));
}
function __rs_success($messages = [], $data = []){
	echo _rs_success($messages,$data);
	die();
}
function rs_error($messages = [], $data = []){
	if(!is_array($messages))
		$messages = [$messages];
	if(!is_array($data))
		$data = [$data=>true];
	return ['success'=>false, 'error'=>true, 'messages'=>$messages, 'data'=>$data];
}
function _rs_error($messages = [], $data = []){
	return json_encode(rs_error($messages,$data));
}
function __rs_error($messages = [], $data = []){
	echo _rs_error($messages,$data);
	die();
}

function create_token($data = [], $url = TRUE){
	if(!is_array($data))
		$data = [$data];
	$data['enc_timestamp'] = time();
	
	$CI =& get_instance();
	if(!@$CI->encrypt)
		$CI->load->library('encrypt');
	$key = $CI->encrypt->encode(json_encode($data));
	if($url)
		$key = urlencode($key);
	return $key;
}
function decode_token($key, $url = FALSE){
	define('TOKEN_LIFESPAN',86400);
	$CI =& get_instance();
	if(!@$CI->encrypt)
		$CI->load->library('encrypt');
	
	if($url)
		$key = urldecode($key);
	$data = json_decode($CI->encrypt->decode($key), true);
	
	if(!is_array($data) || !isset($data['enc_timestamp']))
		return rs_error('Invalid token','invalid');
	
	if($data['enc_timestamp'] < time() - TOKEN_LIFESPAN)
		return rs_error('Your token has expired.','expired');
	
	else
		return rs_success('Valid Token',$data);
}

// VALIDATIONS
function valid_email($email){
	return filter_var($email,FILTER_VALIDATE_EMAIL);
}
function valid_username($username,$return_message = FALSE){
	$message = NULL;
	if(strlen($username) < 6)
		$message = "Username must be at least 6 characters.";
	
	if(!$message)
		return true;
	else if(!$return_message)
		return false;
	else 
		return $message;
}
function valid_password($password,$return_message = FALSE){
	$message = NULL;
	if(strlen($password) < 6)
		$message = "Password must be at least 6 characters, include a number, letter, and pony.";
	
	if(!$message)
		return true;
	else if(!$return_message)
		return false;
	else 
		return $message;
}