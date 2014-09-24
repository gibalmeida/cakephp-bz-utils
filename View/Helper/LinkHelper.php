<?php
/**
 * Copyright 2011 - 2014, Florian Krämer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2011 - 2014, Florian Krämer
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AppHelper', 'View/Helper');

class LinkHelper extends AppHelper {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html'
	);

/**
 * Convenience method to add a title
 *
 * @param string $title
 * @param array $data
 * @param string $identifier
 * @param array $options
 * @return string
 */
	public function titleLink($title, $data, $identifier, $options) {
		$options['linkTitle'] = $title;
		return $this->link($data, $identifier, $options);
	}

/**
 * Creates a link based on a template and data
 *
 * @param array $data
 * @param string $identifier
 * @param array $options
 * @throws RuntimeException
 * @return string
 */
	public function link($data, $identifier, $options = array()) {
		$preset = $this->_getPreset($identifier);
		if (isset($preset['titleField'])) {
			$title = Hash::get($data, $preset['titleField']);
		} elseif (isset($options['linkTitle'])) {
			$title = $options['linkTitle'];
			unset($options['linkTitle']);
		} else {
			throw new \RuntimeException(__('Missing title!'));
		}
		if (isset($options['alias'])) {
			$preset['alias'] = $options['alias'];
			unset($options['alias']);
		}
		$url = $this->buildUrl($data, $preset);
		return $this->Html->link($title, $url, $options);
	}

/**
 * Gets a preset configuration array
 *
 * @param string
 * @return array
 */
	protected function _getPreset($identifier) {
		return (array)Configure::read('App.linkMap.' . $identifier);
	}

/**
 * Builds an URL array based on a preset
 *
 * @param array $data
 * @param string $identifier
 * @throws RuntimeException
 * @throws InvalidArgumentException
 * @return array
 */
	public function buildUrl($data, $identifier) {
		if (is_string($identifier)) {
			$preset = $this->_getPreset($identifier);
		} elseif (is_array($identifier)) {
			$preset = $identifier;
		} else {
			throw new InvalidArgumentException(__('Must be string or array!'));
		}

		$urlVars = array();
		foreach ($preset['fieldMap'] as $urlVar => $field) {
			if (isset($preset['alias'])) {
				$field = str_replace('{alias}', $preset['alias'], $field);
			}
			$result = Hash::get($data, $field);
			if (!is_null($result)) {
				$urlVars[$urlVar] = $result;
			} else {
				throw new \RuntimeException(__('Missing field %s!', $field));
			}
		}
		return Hash::merge($preset['preset'], $urlVars);
	}
}