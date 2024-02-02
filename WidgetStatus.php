<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetStatus extends WidgetList
{
	protected $sort = 'mtime';

	protected function statusWhat()
	{
		return 1;
	}

	public function setSort($value)
	{
		$this->sort = $value;
		return $this;
	}

	public function reload()
	{
		$this->items = [];
		foreach(Git::status($this->statusWhat()) as $name => $value) {
			$path = Git::$dir . '/' . $name;
			$this->items[] = [
				'label' => $name,
				'value' => $value,
				'path' => $path,
				'mtime' => filemtime($path),
			];
		}

		if ($this->sort === 'mtime') {
			usort($this->items, function($a, $b) {
				if ($a['mtime'] < $b['mtime']) return 1;
				if ($a['mtime'] > $b['mtime']) return -1;
				return 0;
			});
		}

		elseif ($this->sort === 'path') {
			usort($this->items, function($a, $b) {
				if ($a['path'] < $b['path']) return -1;
				if ($a['path'] > $b['path']) return 1;
				return 0;
			});
		}

		elseif ($this->sort === 'value') {
			usort($this->items, function($a, $b) {
				if ($a['value'] < $b['value']) return -1;
				if ($a['value'] > $b['path']) return 1;
				return 0;
			});
		}

		return $this;
	}

	public function afterRenderItem($x, $y, $item, $selected, $text)
	{
		if (is_array($item)) {
			$file = Git::$dir . '/' . $item['label'];
			$time = $item['mtime'];

			$date = date('H:i', $time);
			$datef = date('d.m.Y', $time);
			if (date('d.m.Y') != $datef) {
				$date = $datef;
			}

			$x = $x + strlen($text) - strlen($date) - 1;
			t($x, $y, ' ' . $date);
		}
	}


	public function labelFor($item)
	{
		$v = $item['value'];
		while (strlen($v) < 4) $v .= ' ';
		$text = $v . $item['label'];
		return $text;
	}

	public function colorFor($item)
	{
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function currentFile()
	{
		if ($item = $this->getItem($this->index)) {
			return Git::$dir . '/' . $item['label'];
		}
		return false;
	}

	public function actionUndo()
	{
		gui()->action('dialog');
		if ($item = w('status')->currentItem()) {
			if ($status = $item['value'] ?? false) {
				$message = $status == '??' ? 'to delete this file' : 'to undo changes';
				w('dialog')->confirm("Are you sure you want {$message} (Enter, Esc)?"
					,function($dialog) use($item) {
						if ($item['value'] == '??') {
							@unlink($item['path']);
						}
						else {
							gui()->process("git checkout -- " . $item['path']);
						}
						gui()->action('status');
					}

					,function($dialog)  {
						gui()->action('status');
					}
				);
			}
		}
	}

	public function keyPress($key)
	{
		if ($key == 'Enter') {
			if ($file = $this->currentFile()) {
				shell_exec("git add {$file}");
				GitGui::reload();
			}
		}
		elseif ($key == '1')  {
			w('status')->setSort('mtime')->reload();
			App::render();
		}
		elseif ($key == '2')  {
			w('status')->setSort('path')->reload();
			App::render();
		}
		elseif ($key == '3')  {
			w('status')->setSort('value')->reload();
			App::render();
		}
		elseif ($key == 'F8')  {
			$this->actionUndo();
		}
		elseif ($key == 'Tab' || $key == 'Ctrl+Right' || $key == 'Right')  {
			gui()->action('index');
			w('index')->diffFile();
		}
		elseif ($key == 'F2')  {
			gui()->actionCommit();
		}
		elseif ($key == 'F5')  {
			gui()->actionPull();
		}
		elseif ($key == 'F9')  {
			gui()->actionPush();
		}
		else {
			parent::keyPress($key);
		}
	}

	public function diffFile()
	{
		if ($this->active) {
			if ($file = $this->currentFile()) {
				w('dialog')->setFile($file);
				w('diff')->setFile($file);
			}
		}
	}

	public function render()
	{
		parent::render();
		$this->diffFile();
		return $this;
	}

	public function onFocus()
	{
		$this->diffFile();
	}

	public function help()
	{
		return [
			'1' => 'Sort:time',
			'2' => 'Sort:name',
			'F2' => 'Commit',
			'F5' => 'Pull',
			'F8' => 'Undo changes',
			'F9' => 'Push',
		];
	}

}
