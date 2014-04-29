<?php

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	class qa_banned_words_filter {

		var $directory;
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
		}

		function admin_form(&$qa_content)
		{
			$saved = false;

			if (qa_clicked('akismet_save_button')) {
				qa_opt('enable_banned_words_filter_q', (int)qa_post_text('enable_banned_words_filter_q'));
				qa_opt('enable_banned_words_filter_a', (int)qa_post_text('enable_banned_words_filter_a'));
				qa_opt('enable_banned_words_filter_c', (int)qa_post_text('enable_banned_words_filter_c'));
				$words = qa_post_text('enable_banned_words_filter_words');
				qa_opt('enable_banned_words_filter_words_raw', $words);
				qa_opt('enable_banned_words_filter_words', implode(',' , preg_split('/'.QA_PREG_BLOCK_WORD_SEPARATOR.'+/', $words, -1, PREG_SPLIT_NO_EMPTY) ));

				$saved=true;
			}

			qa_set_display_rules($qa_content, array(
				'akismet_user_points_display' => 'akismet_user_points_moderation_on_field',
			));

			return array(
				'ok' => $saved ? 'Akismet settings saved' : null,

				'fields' => array(
					array(
						'label' => 'Send questions containing banned words to moderation queue',
						'value' => qa_opt('enable_banned_words_filter_q'),
						'tags' => 'NAME="enable_banned_words_filter_q"',
						'type' => 'checkbox',
						'value' => (int)qa_opt('enable_banned_words_filter_q'),
					),
					array(
						'label' => 'Send answers containing banned words to moderation queue',
						'value' => qa_opt('enable_banned_words_filter_a'),
						'tags' => 'NAME="enable_banned_words_filter_a"',
						'type' => 'checkbox',
						'value' => (int)qa_opt('enable_banned_words_filter_a'),
					),
					array(
						'label' => 'Send comments containing banned words to moderation queue',
						'value' => qa_opt('enable_banned_words_filter_c'),
						'tags' => 'NAME="enable_banned_words_filter_c"',
						'type' => 'checkbox',
						'value' => (int)qa_opt('enable_banned_words_filter_c'),
					),
					array(
						'label' => 'List of banned words',
						'tags' => 'NAME="enable_banned_words_filter_words"',
						'value' => qa_opt('enable_banned_words_filter_words_raw'),
						'type' => 'textarea',
						'note' => 'separate words with comma, space, or enter',
						'rows' => 4
					)

				),

				'buttons' => array(
					array(
						'label' => 'Save Changes',
						'tags' => 'NAME="akismet_save_button"',
					),
				),
			);
		}

		function bwf_block_words_to_preg($words){
			if( function_exists('qa_block_words_to_preg') )
				return qa_block_words_to_preg($words);
			else{
				$blockwords=qa_block_words_explode($wordsstring);
				$patterns=array();
				
				foreach ($blockwords as $blockword) { // * in rule maps to [^ ]* in regular expression
					$pattern=str_replace('\\*', '[^ ]*', preg_quote(qa_strtolower($blockword), '/'));
					
					if (!preg_match('/^('.QA_PREG_CJK_IDEOGRAPHS_UTF8.')/', $blockword))
						$pattern='(?<= )'.$pattern; // assert leading word delimiter if pattern does not start with CJK
						
					if (!preg_match('/('.QA_PREG_CJK_IDEOGRAPHS_UTF8.')$/', $blockword))
						$pattern=$pattern.'(?= )'; // assert trailing word delimiter if pattern does not end with CJK
						
					$patterns[]=$pattern;
				}
				
				return implode('|', $patterns);
			}
		}

		function filter_question(&$question, &$errors, $oldquestion)
		{
			if (qa_opt('enable_banned_words_filter_q')){
				// combine question title and content for spam processing
				$combined_content = $question['title'].' '.$question['text'];
				$words = qa_opt('enable_banned_words_filter_words');
				$word_list = $this->bwf_block_words_to_preg($words);
				$matched = qa_block_words_match_all($combined_content, $word_list);

				if (!( empty($matched) ))
					$question['queued']=true;

				// tags
				$tags = $question['tags']; //qa_tagstring_to_tags($question['tags']);
				foreach ($tags as $tag) 
					if (count(qa_block_words_match_all($tag, $word_list)))
						$question['queued']=true;

			}
		}

		function filter_answer(&$answer, &$errors, $question, $oldanswer)
		{
			if( qa_opt('enable_banned_words_filter_a') ){
				$words = qa_opt('enable_banned_words_filter_words');
				$word_list = $this->bwf_block_words_to_preg($words);
				$matched = qa_block_words_match_all($answer['text'], $word_list);
				if (!( empty($matched) ))
					$answer['queued']=true;
			}
		}


		function filter_comment(&$comment, &$errors, $question, $parent, $oldcomment)
		{
			if( qa_opt('enable_banned_words_filter_c') ){
				$words = qa_opt('enable_banned_words_filter_words');
				$word_list = $this->bwf_block_words_to_preg($words);
				$matched = qa_block_words_match_all($comment['text'], $word_list);
				if (!( empty($matched) ))
					$comment['queued']=true;
			}
		}
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/