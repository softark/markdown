<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt / 2015 Nobuo Kihara
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\creole\block;

/**
 * Adds the list blocks
 */
trait ListTrait
{
	/**
	 * @var int the current depth of the nested lists
	 */
	private $_listDepth = 1;

	/**
	 * @var array the types of the nested lists
	 */
	private $_nestedListTypes = [];

	/**
	 * identify a line as the beginning of an ordered list.
	 */
	protected function identifyOl($line)
	{
		return preg_match('/^\s*#{' . $this->_listDepth . '}[^#]+/', $line);
	}

	/**
	 * identify a line as the beginning of an unordered list.
	 */
	protected function identifyUl($line)
	{
		return preg_match('/^\s*\*{' . $this->_listDepth . '}[^\*]+/', $line);
	}

	/**
	 * check if a line is an item that belongs to a parent list
	 * @param $line
	 * @return bool true if the line is an item of a parent list
	 */
	protected function isParentItem($line)
	{
		if ($this->_listDepth === 1 || ($marker = $line[0]) !== '*' && $marker !== '#') {
			return false;
		}
		$depthMax = $this->_listDepth - 1;
		if (preg_match('/^(#{1,' . $depthMax . '})[^#]+/', $line, $matches)) {
			return $this->_nestedListTypes[strlen($matches[1])] === 'ol';
		}
		if (preg_match('/^(\*{1,' . $depthMax . '})[^\*]+/', $line, $matches)) {
			return $this->_nestedListTypes[strlen($matches[1])] === 'ul';
		}
		return false;
	}

	/**
	 * check if a line is an item that belongs to a sibling list
	 * @param $line
	 * @return bool true if the line is an item of a sibling list
	 */
	protected function isSiblingItem($line)
	{
		$siblingMarker = $this->_nestedListTypes[$this->_listDepth] == 'ol' ? '*' : '#';
		if ($line[0] !== $siblingMarker) {
			return false;
		}
		if (($siblingMarker === '#' && preg_match('/^#{' . $this->_listDepth . '}[^#]+/', $line)) ||
			($siblingMarker === '*' && preg_match('/^\*{' . $this->_listDepth . '}[^\*]+/', $line))
		) {
			return true;
		}
		return false;
	}

	/**
	 * Consume lines for an ordered list
	 */
	protected function consumeOl($lines, $current)
	{
		// consume until newline

		$block = [
			'list',
			'list' => 'ol',
			'items' => [],
		];
		return $this->consumeList($lines, $current, $block, 'ol');
	}

	/**
	 * Consume lines for an unordered list
	 */
	protected function consumeUl($lines, $current)
	{
		// consume until newline

		$block = [
			'list',
			'list' => 'ul',
			'items' => [],
		];
		return $this->consumeList($lines, $current, $block, 'ul');
	}

	private function consumeList($lines, $current, $block, $type)
	{
		$this->_nestedListTypes[$this->_listDepth] = $type;
		$item = 0;
		$pattern = $type === 'ul' ? '/^\*{' . $this->_listDepth . '}([^\*]+.*|)$/' : '/^#{' . $this->_listDepth . '}([^#]+.*|)$/';
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = ltrim($lines[$i]);
			if ($line === '' ||
				$this->identifyHeadline($line, $lines, $i) ||
				$this->identifyHr($line, $lines, $i) ||
				$this->isParentItem($line) ||
				$this->isSiblingItem($line)
			) {
				// list ended
				$i--;
				break;
			}
			if (preg_match($pattern, $line)) {
				// match list marker on the beginning of the line
				$line = ltrim(substr($line, $this->_listDepth));
				$block['items'][++$item][] = $line;
			} else {
				$this->_listDepth++;
				if ($this->identifyOl($line)) {
					list($childBlock, $i) = $this->consumeOl($lines, $i);
					$block['items'][$item][] = $childBlock;
				} elseif ($this->identifyUl($line)) {
					list($childBlock, $i) = $this->consumeUl($lines, $i);
					$block['items'][$item][] = $childBlock;
				} else {
					$line = ltrim($line);
					$block['items'][$item][] = $line;
				}
				$this->_listDepth--;
			}
		}

		foreach($block['items'] as $itemId => $itemLines) {
			$content = [];
			$texts = [];
			foreach ($itemLines as $line) {
				if (!isset($line['list'])) {
					$texts[] = $line;
				} else {
					if (!empty($texts)) {
						$content = array_merge($content, $this->parseInline(implode("\n", $texts)));
						$texts = [];
					}
					$content[] = $line;
				}
			}
			if (!empty($texts)) {
				$content = array_merge($content, $this->parseInline(implode("\n", $texts)));
			}
			$block['items'][$itemId] = $content;
		}

		return [$block, $i];
	}

	/**
	 * Renders a list
	 */
	protected function renderList($block)
	{
		$type = $block['list'];
		$output = "<$type>\n";

		foreach ($block['items'] as $item => $itemLines) {
			$output .= '<li>' . $this->renderAbsy($itemLines). "</li>\n";
		}
		return $output . "</$type>\n";
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
}
