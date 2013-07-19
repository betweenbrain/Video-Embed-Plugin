<?php defined('_JEXEC') or die;

/**
 * File       videoembed.php
 * Created    1/17/13 10:22 PM
 * Modified   3/14/13 2:17 PM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/brightcove-player/issues
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

jimport('joomla.plugin.plugin');

class plgSystemVideoembed extends JPlugin {

	function plgSystemVideoembed(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->app = JFactory::getApplication();
		$this->doc = JFactory::getDocument();
	}

	function onAfterRender() {

		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			return TRUE;
		}

		$buffer = JResponse::getBody();

// Regex supports optional third numeric argument
		$pattern         = '/{(Brightcove|Youtube)[^}]*}/i';
		$playerKey       = htmlspecialchars($this->params->get('playerKey'));
		$defaultPlayerId = htmlspecialchars($this->params->get('defaultPlayerId'));
		$defaultWidth    = htmlspecialchars($this->params->get('defaultWidth'));
		$defaultHeight   = htmlspecialchars($this->params->get('defaultHeight'));

		// Find all matches in buffer
		preg_match_all($pattern, $buffer, $matches, PREG_SET_ORDER);

		if ($matches[0]) {

			foreach ($matches as $key => $match) {

				// Remove curly brackets, word Brightcove, and space after it.
				$attributes = str_replace(array('{' . $match[1] . ' ', '}'), '', $match[0]);

				// Turn that string into a thing (array)
				$attributes = explode(' ', $attributes);

				// Set default values that can later be changed
				$videoWidth  = $defaultWidth;
				$videoHeight = $defaultHeight;
				$playerID    = $defaultPlayerId;
				$videoID     = htmlspecialchars($attributes[0]);
				$videoLink   = JURI::current();

				// Remove the video ID from array
				array_shift($attributes);

				// Check remaining bits, if there are any
				if (count($attributes[0])) {
					foreach ($attributes as $attribute) {
						if (strstr($attribute, 'http://')) {
							$videoLink = $attribute;
						} elseif (strstr($attribute, 'x')) {
							$dims        = explode('x', $attribute);
							$videoWidth  = $dims[0];
							$videoHeight = $dims[1];
						} else {
							$playerID = $attribute;
						}
					}
				}

				switch (strtolower($match[1])) {

					case "brightcove" :

						// As $doc->_scripts is already rendered, we need to attach our script to the document
						if (!strpos($buffer, 'BrightcoveExperiences.js')) {
							$buffer = str_replace('</body>', '<script type="text/javascript" src="http://admin.brightcove.com/js/BrightcoveExperiences.js"></script >' . "\n" . '</body>', $buffer);
						}

						$replacement = <<<EOT
			<object id="myExperience$videoID" class="BrightcoveExperience">
			<param name="bgcolor" value="#FFFFFF" />
			<param name="dynamicStreaming" value="true" />
			<param name="height" value="$videoHeight" />
			<param name="isUI" value="true" />
			<param name="isVid" value="true" />
			<param name="linkBaseURL" value="$videoLink" />
			<param name="playerID" value="$playerID" />
			<param name="playerKey" value="$playerKey" />
			<param name="@videoPlayer" value="$videoID" />
			<param name="width" value="$videoWidth" />
			<param name="wmode" value="transparent" />
			</object>
			<script type="text/javascript">brightcove.createExperiences();</script>
EOT;

						break;

					case "youtube" :

						$replacement = <<<EOT
						<div id="ytplayer"></div>

						<script>
						  // Load the IFrame Player API code asynchronously.
						  var tag = document.createElement('script');
						  tag.src = "https://www.youtube.com/player_api";
						  var firstScriptTag = document.getElementsByTagName('script')[0];
						  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

						  // Replace the 'ytplayer' element with an <iframe> and
						  // YouTube player after the API code downloads.
						  var player;
						  function onYouTubePlayerAPIReady() {
						    player = new YT.Player('ytplayer', {
						      height: '$videoHeight',
						      width: '$videoWidth',
						      videoId: '$videoID',
						      playerVars : {
						        'autohide':'1',
						        'modestbranding':'1'
						      }
						    });
						  }
						</script>
EOT;
						break;
				}

				$buffer = str_replace($match[0], $replacement, $buffer);
			}

			JResponse::setBody($buffer);

			return TRUE;
		}

		return FALSE;
	}
}


