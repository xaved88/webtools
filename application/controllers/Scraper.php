<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scraper extends CI_Controller {

	 public function __construct(){
		parent::__construct();
		
		$this->load->model('Scraper_model');
	}
	
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function test(){
		$url = "http://www.skagen.com/us/en/women/products/watches/anita-steel-mesh-multifunction-watch-pdpskw2312p.html?referer=productlisting";
		$rules = [];
		/*
		$rules[] = [
			'name' => 'anchor buttons',
			'selector' => 'a.btn',
			'attr' => 'class'	
		];
		$rules[] = [
			'name' => 'images',
			'selector' => 'img',
			'attr' => 'src'
		];
		*/
		$rules[] = [
			'name' => 'name',
			'selector' => 'h1.product-title',
			'attr' => 'innertext'
		];
		$rules[] = [
			'name' => 'price',
			'selector' => '.product-price',
			'attr' => 'innertext',
			'instance' => 0
		];
		$rules[] = [
			'name' => 'specs',
			'selector' => 'dl.product-specs',
			'attr' => 'innertext',
			'rules' => [
				[	'name' => [
						// 'selector' => 'dt',
						'traverse' => 'prev_sibling',
						'attr' => 'innertext'
					],
					'selector' => 'dd',
					'attr' => 'innertext'
				]			
			]
		];
		$rs = $this->Scraper_model->scrape_site($url,$rules);
		print_r($rs);
		die();
	}
}
