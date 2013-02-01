<?php defined('_JEXEC') or die;

/**
 * File       brightcoveplayer.php
 * Created    1/17/13 10:22 PM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/
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
		$pattern   = '/{Brightcove\s([0-9]+)\s?([0-9]+)?}/i';
		$playerKey = $this->params->get('playerKey');
		$playerID  = $this->params->get('playerID');

		// Find all matches in buffer
		preg_match_all($pattern, $buffer, $matches);

		// Add BrightcoveExperiences script to document head only once in case of multiple matches
		if (count($matches[0])) {
			// As $doc->_scripts is already rendered, we need to attach our script to the head somewhow
			// $doc->_scripts['http://admin.brightcove.com/js/BrightcoveExperiences.js'] = 'text/javascript';
			$buffer = str_replace('</head>', '  <script type="text/javascript" src="http://admin.brightcove.com/js/BrightcoveExperiences.js"></script >' . "\n" . '</head>', $buffer);
		}

		foreach ($matches as $match) {

			$match = str_replace(array('{', '}'), '', $match[0]);

			$match = explode(' ', $match);

			$replacement = <<<EOT
			<object id="myExperience$match[1]" class="BrightcoveExperience">
			<param name="bgcolor" value="#FFFFFF" />
			<param name="width" value="902" />
			<param name="height" value="507" />
			<param name="playerID" value="$playerID" />
			<param name="playerKey" value="$playerKey" />
			<param name="isVid" value="true" />
			<param name="isUI" value="true" />
			<param name="dynamicStreaming" value="true" />
			<param name="@videoPlayer" value="$match[1]" />
			</object>
			<script type="text/javascript">brightcove.createExperiences();</script>
EOT;

			$buffer = preg_replace($pattern, $replacement, $buffer);
		}

		JResponse::setBody($buffer);

		return TRUE;
	}
}


