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
		// $url = "http://www.skagen.com/us/en/women/products/watches/anita-steel-mesh-multifunction-watch-pdpskw2312p.html?referer=productlisting";
		$url = base_url('test.html');
		
		$urls = [];
		$urls[] = "http://www.skagen.com/us/en/women/products/watches/anita-steel-mesh-multifunction-watch-pdpskw2312p.html?referer=productlisting";
		$urls[] = 
		"http://www.skagen.com/us/en/women/products/watches/anita-steel-mesh-multifunction-watch-pdpskw2314p.html?referer=productlisting";
		
		$rules = [];
		/*
		name
		dom
		attr
		instance
		rules
		functions
		
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
			'dom' => [
				'selector' => 'h1.product-title'
			],
			'attr' => 'innertext'
		];
		$rules[] = [
			'name' => 'description',
			'dom' => [
				'selector' => '.product-description'
			],
			'attr' => 'innertext',
			'functions' => [
				[	'type'=>'trim',
					'param'=>['right','36']
				],
				[	'type'=>'uppercase'
				]
			]
		];
		$rules[] = [
			'name' => 'price',
			'dom' => [
				'selector' => '.product-price'
			],
			'attr' => 'innertext',
			'instance' => 0
		];
		$rules[] = [
			'name' => 'specs',
			'dom' => [
				'selector' => 'dl.product-specs'
			],
			'attr' => 'innertext',
			'rules' => [
				[	'name' => [
						// 'selector' => 'dt',
						'dom' => [
							'traverse' => 'prev_sibling'
						],
						'attr' => 'innertext',
						'functions' => [
							[	'type' => 'append', 'param'=>['go for it!']]
						]
					],
					'dom' => [
						'selector' => 'dd'
					],
					'attr' => 'innertext'
				]			
			]
		];

		$rs = $this->Scraper_model->scrape_sites($urls,$rules);
		print_r($rs);
		die();
	}
}
