<?php defined('_JEXEC') or die;

/**
 * File       brightcoveplayer.php
 * Created    1/17/13 10:22 PM
 * Modified   3/12/13 2:17 PM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/brightcove-player/issues
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

jimport('joomla.plugin.plugin');

class plgSystemBrightcoveplayer extends JPlugin {

	function plgSystemBrightcoveplayer(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onAfterRender() {

		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			return TRUE;
		}

		$buffer = JResponse::getBody();

		// Regex supports optional third numeric argument
		$pattern         = '/{Brightcove[\s0-9x]*}/i';
		$playerKey       = htmlspecialchars($this->params->get('playerKey'));
		$defaultPlayerId = htmlspecialchars($this->params->get('defaultPlayerId'));
		$defaultWidth    = htmlspecialchars($this->params->get('defaultWidth'));
		$defaultHeight   = htmlspecialchars($this->params->get('defaultHeight'));

		// Find all matches in buffer
		preg_match_all($pattern, $buffer, $matches);

		// Add BrightcoveExperiences script to document only once in case of multiple matches
		if (count($matches[0])) {
			// As $doc->_scripts is already rendered, we need to attach our script to the document
			$buffer = str_replace('</body>', '<script type="text/javascript" src="http://admin.brightcove.com/js/BrightcoveExperiences.js"></script >' . "\n" . '</body>', $buffer);

			foreach ($matches[0] as $match) {

				// Remove curly brackets, word Brightcove, and space after it.
				$attributes = str_replace(array('{Brightcove ', '{brightcove ', '}'), '', $match);

				// Turn that string into a thing (array)
				$attributes = explode(' ', $attributes);

				// Set default values that can later be changed
				$videoWidth  = $defaultWidth;
				$videoHeight = $defaultHeight;
				$playerID    = $defaultPlayerId;
				$videoID     = $attributes[0];

				// Remove the video ID from array
				array_shift($attributes);

				// Check remaining bits, if there are any
				if (count($attributes[0])) {
					foreach ($attributes as $attribute) {
						if (strstr($attribute, 'x')) {
							$dims        = explode('x', $attribute);
							$videoWidth  = $dims[0];
							$videoHeight = $dims[1];
						} else {
							$playerID = $attribute;
						}
					}
				}

				$replacement = <<<EOT
			<object id="myExperience$videoID" class="BrightcoveExperience">
			<param name="bgcolor" value="#FFFFFF" />
			<param name="width" value="$videoWidth" />
			<param name="height" value="$videoHeight" />
			<param name="playerID" value="$playerID" />
			<param name="playerKey" value="$playerKey" />
			<param name="isVid" value="true" />
			<param name="isUI" value="true" />
			<param name="dynamicStreaming" value="true" />
			<param name="@videoPlayer" value="$videoID" />
			</object>
			<script type="text/javascript">brightcove.createExperiences();</script>
EOT;

				$buffer = str_replace($match, $replacement, $buffer);
			}

			JResponse::setBody($buffer);

			return TRUE;
		}

		return FALSE;
	}
}


