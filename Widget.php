<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class Widget
{
	public $active = false;
	public $title = false;
	public $x1, $y1, $x2, $y2;
	public $disabled = false;

	public function setSize($x1, $y1, $x2, $y2)
	{
		$this->x1 = $x1;
		$this->x2 = $x2;
		$this->y1 = $y1;
		$this->y2 = $y2;
		return $this;
	}

	public function disable()
	{
		$this->disabled = true;
		return $this;
	}

	public function enable()
	{
		$this->disabled = false;
		return $this;
	}

	public function setTitle($value)
	{
		$this->title = $value;
		return $this;
	}

	public function drawFrame()
	{
		if ($this->active) {
			a()->color($this->focusColor());
		} else {
			a()->color($this->blurColor());
		}
		frame($this->x1, $this->y1, $this->x2, $this->y2);
		if ($this->title) {
			$len = strlen($this->title);
			$x = floor($this->x1 + ($this->x2 - $this->x1) / 2 - $len / 2) - 1;
			if ($this->active) {
				a()->color($this->focusTitleColor());
			} else {
				a()->color($this->blurColor());
			}
			t($x, $this->y1, ' ' . $this->title . ' ');
		}
		return $this;
	}

	public function focusColor()
	{
		return [SGR::COLOR_FG_YELLOW_BRIGHT, SGR::COLOR_BG_BLACK];
	}

	public function focusTitleColor()
	{
		return [SGR::COLOR_FG_BLACK, SGR::COLOR_BG_YELLOW_BRIGHT];
	}

	public function blurColor()
	{
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function render()
	{
		$this->drawFrame();
		return $this;
	}

	public function focus()
	{
		$this->active = true;
		$this->onFocus();
	}

	public function blur()
	{
		$this->active = false;
		$this->onBlur();
	}

	public function onFocus()
	{
	}

	public function onBlur()
	{
	}

	public function keyPress($key)
	{
	}

	public function reload()
	{
		return $this;
	}

	public function help()
	{
		return [];
	}
}
