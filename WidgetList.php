<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetList extends Widget
{
	public $items = [];
	public $index = 0;
	public $scroll = 0;

	public function getItem($n)
	{
		return isset($this->items[$n])? $this->items[$n] : false;
	}

	public function labelFor($item)
	{
		return $item['label'];
	}

	public function currentItem()
	{
		return $this->getItem($this->index);
	}

	public function colorFor($item)
	{
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function selectedColorFor($item)
	{
		return [SGR::COLOR_FG_BLACK, SGR::COLOR_BG_BLACK_BRIGHT];
	}

	public function render()
	{
		$this->drawFrame();
		$h = $this->y2 - $this->y1 - 1;
		if ($this->index >= count($this->items)) {
			$this->index = count($this->items) - 1;
		}
		if ($this->index < 0) {
			$this->index = 0;
		}
		while ($this->scroll < $this->index - $h + 1) {
			$this->scroll++; 
		}
		while ($this->scroll > 0 && $this->index < $this->scroll) {
			$this->scroll--;
		}
		for($i = 0; $i < $h; $i++) {
			$item = $this->getItem($this->scroll + $i);
			$this->renderItem($this->x1 + 1, $this->y1 + $i + 1, $item, $this->index == $this->scroll + $i);
		}
		return $this;
	}

	public function renderItem($x, $y, $item, $selected)
	{
		$text = $item ? $this->labelFor($item) : '';
		while (strlen($text) < $this->x2 - $this->x1 - 1) $text .= ' ';

		if ($item) {
			if ($selected && $this->active) {
				a()->color($this->selectedColorFor($item));
			} else {
				a()->color($this->colorFor($item));
			}
		} else {
			a()->color([SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK]);
		}
		t($x, $y, $text);
		$this->afterRenderItem($x, $y, $item, $selected, $text);
	}

	public function afterRenderItem($x, $y, $item, $selected, $text)
	{
	}

	public function keyPress($key)
	{
		if ($key == 'Down') {
			$this->index++;
		}
		if ($key == 'Up') {
			$this->index--;
		}
		if ($key == 'Home') {
			$this->index = 0;
		}
		if ($key == 'End') {
			$this->index = count($this->items) - 1;
		}
		if ($key == 'PageDown') {
			$this->index += ($this->y2 - $this->y1 - 2);
		}
		if ($key == 'PageUp') {
			$this->index -= ($this->y2 - $this->y1 - 2);
		}
		$this->render();
	}
}