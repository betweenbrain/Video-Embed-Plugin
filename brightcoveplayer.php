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

class plgContentBrightcoveplayer extends JPlugin {

	function plgSystemBrightcoveplayer(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onPrepareContent(&$article, &$params, $limitstart) {

		$app = JFactory::getApplication();

		if ($app->isAdmin()) {
			return TRUE;
		}

		$doc       = JFactory::getDocument();
		// Regex supports option third numeric argument
		$pattern   = '/{Brightcove\|([0-9]+)\|?([0-9]+)?}/i';
		$playerKey = $params->get('playerKey');
		$playerID  = $params->get('playerID');

		// Add remote script to document head once in case of multiple cases
		preg_match_all($pattern, $article->text, $matches);
		$count = count($matches[0]);
		if ($count) {
			$doc->addScript('http://admin.brightcove.com/js/BrightcoveExperiences.js');
		}

		$replacement = '<object class="BrightcoveExperience" >';
		$replacement .= '<param name="bgcolor" value="#FFFFFF" />';
		$replacement .= '<param name="width" value="480" />';
		$replacement .= '<param name="height" value="270" />';
		$replacement .= '<param name="playerID" value="' . $playerID . '" />';
		$replacement .= '<param name="playerKey" value="' . $playerKey . '" />';
		$replacement .= '<param name="isVid" value="TRUE" />';
		$replacement .= '<param name="isUI" value="TRUE" />';
		$replacement .= '<param name="dynamicStreaming" value="TRUE" />';
		$replacement .= '<param name="@videoPlayer" value="$1" />';
		$replacement .= '</object >';
		$replacement .= '<script type="text/javascript">brightcove.createExperiences();</script >';

		$article->text = preg_replace($pattern, $replacement, $article->text);

		return TRUE;
	}
}


