<?php

/*
	Plugin Name: Banned Words Filter
	Plugin URI:  http://qa-themes.com/banned-words-filter
	Plugin Update Check URI: https://github.com/Towhidn/Q2A-Banned-Words-filter/raw/master/banned-words-filter/qa-plugin.php
	Plugin Description: detects new posts with banned keywords and send them to moderation queue
	Plugin Version: 1.0
	Plugin Date: 2014-29-4
	Plugin Author: QA-Themes.com
	Plugin Author URI: http://QA-Themes.com
	Plugin Minimum Question2Answer Version:
	Plugin Minimum PHP Version:
	Plugin License: copy lifted          
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	qa_register_plugin_module('filter', 'banned-words-filter.php', 'qa_banned_words_filter', 'Banned Words Filter');

/*
	Omit PHP closing tag to help avoid accidental output
*/