<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetView extends Widget
{
	public $items = [];
	public $scroll = 0;

	public function render()
	{
		$h = $this->y2 - $this->y1 - 1;

		if (count($this->items) <= $h) {
			$this->scroll = 0;
		} else {
			while ($this->scroll > 0 && $this->scroll > count($this->items) - $h) {
				$this->scroll--;
			}
			if ($this->scroll < 0) {
				$this->scroll = 0;
			}
		}

		$this->drawFrame();
		for($i = 0; $i < $h; $i++) {
			$line = $this->getLine($this->scroll + $i);
			$this->renderLine($this->x1 + 1, $this->y1 + $i + 1, $line);
		}
		return $this;
	}

	public function clear()
	{
		$this->items = [];
		return $this;
	}

	public function addLine($s)
	{
		$this->items[] = $s;
		return $this;
	}

	public function scrollEnd()
	{
		$this->scroll = count($this->items);
		return $this->render();
	}

	public function getLine($n)
	{
		return isset($this->items[$n])? $this->items[$n] : false;
	}

	public function renderLine($x, $y, $text)
	{
		$text = str_replace("\t", '    ', $text);
		while (strlen($text) < $this->x2 - $this->x1 - 1) $text .= ' ';
		$text = substr($text, 0, $this->x2 - $this->x1 - 1);
		$color = $this->colorFor($text);
		a()->color($color);
		t($x, $y, $text);
	}

	public function colorFor($text)
	{
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function keyPress($key)
	{
		if ($key == 'Down') {
			$this->scroll++;
		}
		if ($key == 'PageDown') {
			$this->scroll += ($this->y2 - $this->y1 - 2);
		}
		if ($key == 'Up') {
			$this->scroll--;
		}
		if ($key == 'PageUp') {
			$this->scroll -= ($this->y2 - $this->y1 - 2);
		}
		if ($key == 'Home') {
			$this->scroll = 0;
		}
		if ($key == 'End') {
			$this->scroll = count($this->items);
		}
		return $this->render();
	}

}