<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetText
{
    public $x = 1;
    public $y = 1;
	public $text = '';
	public $disabled = false;

	public function setPos($x, $y)
	{
		$this->x = $x;
		$this->y = $y;
		return $this;
	}

	public function setText($v)
	{
		$this->text = $v;
		return $this;
	}

	public function color()
	{
		return [SGR::COLOR_FG_GREEN, SGR::COLOR_BG_BLACK];
	}

	public function render()
	{
		a()->color($this->color());
		t($this->x, $this->y, $this->text);
		return $this;
	}

	public function blur() {
	}

	public function focus() {
	}
}