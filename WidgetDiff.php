<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetDiff extends WidgetView
{
	public $file = false;

	public function setFile($file)
	{
		if ($file && $file != $this->file) {
			$this->file = $file;
			$this->scroll = 0;

			if (is_file($file)) {
				$do = false;
				$this->items = [];
				$status = Git::getFileStatus($file);
				if ($status == '??') {
					$diff = file_get_contents($file);
					$do = true;
				}
				elseif ($status[0] == 'M') {
					$diff = shell_exec("git diff --cached {$file}");
				}
				else {
					$diff = shell_exec("git diff {$file}");
				}
			} else {
				$diff = 'File not exists';
				$this->items = [];
				$do = true;
			}

			foreach(explode("\n", $diff) as $line) {
				$line = rtrim($line);
				if (substr($line, 0, 2) == '@@') {
					$do = true;
				}
				if ($do) {
					$this->items[] = $line;
				}
			}
		}
		return $this->render();
	}

	public function colorFor($text)
	{
		if (substr($text, 0, 2) == '@@') {
			return [SGR::COLOR_FG_BLUE_BRIGHT, SGR::COLOR_BG_BLACK];
		}
		if (substr($text, 0, 1) == '-') {
			return [SGR::COLOR_FG_RED_BRIGHT, SGR::COLOR_BG_BLACK];
		}
		if (substr($text, 0, 1) == '+') {
			return [SGR::COLOR_FG_GREEN_BRIGHT, SGR::COLOR_BG_BLACK];
		}
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function keyPress($key)
	{
		if ($key == 'Ctrl+Down' || $key == 'Ctrl+Up' || $key == 'Ctrl+Home' || $key == 'Ctrl+End' || $key == 'Ctrl+PageUp' || $key == 'Ctrl+PageDown') {
			return parent::keyPress(substr($key, 5));
		}
	}

}