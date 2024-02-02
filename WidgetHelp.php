<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetHelp
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

	public function color1()
	{
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function color2()
	{
		return [SGR::COLOR_FG_BLACK, SGR::COLOR_BG_WHITE];
	}

	public function render()
	{
		a()->color($this->color1());
		$x = $this->x;
		t($x, $this->y, ' ');
		foreach(gui()->activeWidget()->help() as $k => $label) {
			a()->color($this->color1());
			t($x, $this->y, $k);
			$x += mb_strlen($k);
			a()->color($this->color2());
			t($x, $this->y, ' ' . $label . ' ');
			$x += mb_strlen($label) + 2;
			a()->color($this->color1());
			t($x, $this->y, '   ');
			$x++;
		}
		a()->color($this->color1());
		while ($x < App::$w) {
			$x++;
			t($x, $this->y, ' ');
		}
		return $this;
	}

	public function blur() {
	}

	public function focus() {
	}
}