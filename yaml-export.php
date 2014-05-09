<?php

/**
 * Plugin Name: WP Export to YAML
 * Plugin URI: https://zombo.com
 * Description: Setting to create an export YAML for Posts and Pages
 *
 * Author:      ASMBS
 * Author URI:  https://github.com/asmbs
 *
 * License:     MIT License
 * License URI: http://opensource.org/licenses/MIT
 *
 * Version:     1.0.0-beta
 */
class YAML_export {

	/* Construction of Class */

	private static $_get_instance = NULL;
	public function __construct(){
		add_action('admin_init', function(){
			$this->register_YAML_setting();
		});

		add_action('admin_menu', function(){
			$this->yaml_add_menu_page();	
		});

	}
	public function get_instance()
	{
		if (self::$_get_instance == NULL )
			self::$_get_instance = new self;

		return self::$_get_instance;
	}
	/* Add class to Menu */

	public function yaml_add_menu_page(){
		add_options_page('Export to YAML', 'Export to YAML', 'administrator', __FILE__, function(){
			do_settings_sections(__FILE__);
		});
	}
	public function register_YAML_setting(){
		register_setting('yaml_export', 'yaml_export');	
		add_settings_section( 'yaml_export_section', 'Export to YAML', array($this,'yaml_add_settings'), __FILE__ );
		add_settings_field('yaml_export_button', 'Export now: ', array($this,'yaml_add_export_field'), __FILE__, 'yaml_export_section');
	}


	public function yaml_add_settings(){


	}

	public function yaml_add_export_field(){

		$this->execute_query();
		echo '<h2>';

	}

// The query mamma jamma itself

	public function execute_query(){
//Make sure appropriate directories exist
		$directory = '/users/erik/query_outputs/';
		if (!is_dir($directory)){
			mkdir($directory);
			mkdir($directory . "/htmls");
			mkdir($directory . "/mds");
			mkdir($directory . "/yamls");
			mkdir($directory . "/concats");

		}

		$args = array(
			'post_type' => 'post', 'page',
			'posts_per_page' => -1,
			);
		$keywords = $this->generate_keywords();
		$the_query = new WP_Query($args);
		if ($the_query->have_posts())
		{
			while ($the_query->have_posts()){
				$post = $the_query->the_post();
				$title = get_the_title();
				$date = get_the_date();
				$categories = get_the_category();
				$single_cat = $categories[0]->cat_name;
				$content = get_the_content();
				$content = apply_filters('the_content', $content);
				$generated_categories = $this->generate_categories($content, $keywords);
				$yaml_file = <<< EOT
----
title: $title
date: $date
categories: $single_cat, $generated_categories[0], $generated_categories[1], $generated_categories[2]
----

EOT;
				$file_title = substr(str_replace(' ', '', $title), 0, 30);
				$filename = $directory . "htmls/" . $file_title . ".html";
				$yamlname =  $directory . "yamls/" . $file_title . ".yaml";
				file_put_contents($yamlname, $yaml_file);
				file_put_contents($filename, $content);			

			}

		}

	}

	//Category generation function
	public function generate_categories($content, $keywords){
		/*
		Take the list of Keywords
		Count the number of occurrences of each
		Merge the numbers for alternate titles
		Identify the top three
		return those as categories
		*/
		$categorycounts = array();
		$topcategories = array();
		$lcontent = strtolower($content);
		foreach ($keywords as $keygroup){
			$count = 0;
			foreach($keygroup as $keyword){
				$num = substr_count($lcontent, $keyword);
				$count += $num;
			}
			$catarray = array(
				'keyword' => $keygroup[0],
				'count' => $count,
				);
			array_push($categorycounts, $catarray);
		}
		usort($categorycounts, function ($cat1, $cat2) {
   		 return $cat2['count'] - $cat1['count'];
		});
		$categorycounts = array_slice($categorycounts, 0, 3);
		var_dump($categorycounts);
		foreach ($categorycounts as $category){
			if ($category['count'] > 5){
				array_push($topcategories, $category['keyword']);
			}
		}
		return $topcategories;
	}
	public function generate_keywords(){
		// CSVs should be formatted as such:
		// The first item in a row is the "true keyword" - this is what you want to display
		// The second and third items are "alternate keywords" - these are synonyms or alternate terms for the core keyword

		$keywords = array();
		$csv = fopen('/users/erik/asmbs_keywords.csv','r');
		while (($data= fgetcsv($csv, 1000, ',')) !== FALSE){
			$keyword = array($data[0], $data[1], $data[2]);
			array_push($keywords, $keyword);
		}
		return $keywords;
	}

}
YAML_export::get_instance();




