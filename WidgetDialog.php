<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetDialog extends Widget
{
	public $mode = false;
	public $file = false;
	public $message = '';
	public $onOk = false;
	public $onCansel = false;
	public $inputText = '';
	public $cursorPos = 0;
	public $editStart = false;

	public function setFile($v)
	{
		$r = ($v != $this->file);
		$this->file = $v;
		if ($r) $this->render();
		return $this;
	}

	public function confirm($message, $yes = false, $no = false)
	{
		$this->onOk = $yes;
		$this->onCancel = $no;
		$this->message = $message;
		$this->mode = 'confirm';
		$this->render();
	}

	public function prompt($message, $initText, $yes = false, $no = false)
	{
		$this->onOk = $yes;
		$this->onCancel = $no;
		$this->message = $message;
		$this->mode = 'prompt';
		$this->inputText = $initText;
		$this->cursorPos = 0;
		$this->editStart = false;
		$this->render();
	}

	public function render()
	{
		$this->drawFrame();
		if ($this->mode == 'confirm') {
			$this->renderConfirm();
		}
		elseif ($this->mode == 'prompt') {
			$this->renderPrompt();
		}
		else {
			if ($this->file) {
				$this->renderFile();
			} else {
				$this->renderClear();
			}
		}
		return $this;
	}

	public function messageColor()
	{
		return [SGR::COLOR_FG_WHITE_BRIGHT, SGR::COLOR_BG_BLACK];
	}

	public function inputColor()
	{
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function cursorColor()
	{
		return [SGR::COLOR_FG_BLACK, SGR::COLOR_BG_YELLOW];
	}

	public function renderConfirm()
	{
		a()->color($this->messageColor());
		$text = $this->message;
   		while (strlen($text) < $this->x2 - $this->x1 - 3) $text .= " ";
		t($this->x1 + 1, $this->y1 + 1, $text);
	}

	public function renderClear()
	{
		$pos = $this->x1 + 1;
		t($this->x1 + 1, $this->y1 + 1, '');
		while ($pos < $this->x2 - 1) {
			a()->text(' ');
			$pos++;
		}
	}

	public function renderFile()
	{
		$pos = $this->x1 + 1;
		t($this->x1 + 1, $this->y1 + 1, '');

		if (is_file($this->file)) {

    		$text = date('Y-m-d H:i ', filemtime($this->file));
			a()->color([SGR::COLOR_FG_GREEN_BRIGHT, SGR::COLOR_BG_BLACK]);
			a()->text($text);
			$pos += strlen($text);

			

    		$text = filesize($this->file);
    		while (strlen($text) < 12) $text = " {$text}";
    		$text .= ' ';
			a()->color([SGR::COLOR_FG_YELLOW_BRIGHT, SGR::COLOR_BG_BLACK]);
			a()->text($text);
			$pos += strlen($text);

			$text = substr($this->file, strlen(Git::$dir) + 1);
			a()->color([SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK]);
			a()->text($text);
			$pos += strlen($text);

		}

		while ($pos < $this->x2 - 1) {
			a()->text(' ');
			$pos++;
		}
	}

	public function renderPrompt()
	{
		$pos = 0;
		a()->color($this->messageColor());
		t($this->x1 + 1, $this->y1 + 1, $this->message . ' ');
		$pos += mb_strlen($this->message) + 1;

		if ($this->cursorPos < 0) {
			$this->cursorPos = 0;
		}

		while ($this->cursorPos > mb_strlen($this->inputText)) {
			$this->cursorPos--;
		}

		if ($this->cursorPos > 0) {
			a()->color($this->inputColor());
			$text = mb_substr($this->inputText, 0, $this->cursorPos);
			$pos += $this->cursorPos;
			a()->text($text);
		}

		if ($this->cursorPos <= strlen($this->inputText)) {
			a()->color($this->cursorColor());
			$text = mb_substr($this->inputText, $this->cursorPos, 1);
			if (empty($text)) $text = ' ';
			$pos += 1;
			a()->text($text);
		}

		if ($this->cursorPos < strlen($this->inputText)) {
			a()->color($this->inputColor());
			$text = mb_substr($this->inputText, $this->cursorPos + 1);
			$pos += mb_strlen($text);
			a()->text($text);
		}

//			a()->color($this->inputColor());
//			$text = trim($this->cursorPos);
//			$pos += mb_strlen($text);
//			a()->text($text);


		a()->color([SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK]);
		while ($pos < $this->x2 - 2) {
			a()->text(' ');
			$pos++;
		}

	}

	public function keyPress($key)
	{
		if ($this->mode == 'confirm') {
			if ($key == 'Esc') {
				$this->mode = false;
				if (is_callable($this->onCancel)) {
					call_user_func($this->onCancel, $this);
				}
			}
			elseif ($key == 'Enter') {
				$this->mode = false;
				if (is_callable($this->onOk)) {
					call_user_func($this->onOk, $this);
				}
			}
		}

		elseif ($this->mode == 'prompt') {
			if ($key == 'Esc') {
				$this->mode = false;
				if (is_callable($this->onCancel)) {
					call_user_func($this->onCancel, $this);
				}
			}
			elseif ($key == 'Enter') {
				$this->mode = false;
				if (is_callable($this->onOk)) {
					call_user_func($this->onOk, $this);
				}
			}
			elseif ($key == 'Right') {
				$this->cursorPos++;
				$this->render();
			}
			elseif ($key == 'Left') {
				$this->cursorPos--;
				$this->render();
			}
			elseif ($key == 'Home') {
				$this->cursorPos = 0;
				$this->render();
			}
			elseif ($key == 'End') {
				$this->cursorPos = mb_strlen($this->inputText);
				$this->render();
			}
			elseif ($key == 'Bs') {
				if ($this->cursorPos > 0) {
					$this->cursorPos--;
					$this->inputText = mb_substr($this->inputText, 0, $this->cursorPos) . mb_substr($this->inputText, $this->cursorPos + 1);
					$this->render();
				}
			}
			elseif ($key == 'Del') {
				$this->inputText = mb_substr($this->inputText, 0, $this->cursorPos) . mb_substr($this->inputText, $this->cursorPos + 1);
				$this->render();
			}
			else {
				if (is_string($key) && mb_strlen($key) == 1) {
					$s1 = $this->cursorPos > 0? mb_substr($this->inputText, 0, $this->cursorPos) : '';
					$s2 = mb_substr($this->inputText, $this->cursorPos);
					$this->inputText = $s1 . $key . $s2;
					$this->cursorPos++;
					$this->render();
				}
			}
		}
	}

}