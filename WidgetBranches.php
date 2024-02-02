<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetBranches extends WidgetList
{
	public $upstreams;
	public $currentBranch = false;

	public function reload()
	{
		$this->items = [];
		foreach(Git::branches() as $name => $current) {
			$this->items[] = [
				'label' => $name,
				'value' => $current,
			];
			if ($current) {
				$this->currentBranch = $name;
			}
		}
		$this->index = 0;
		$this->scroll = 0;
		$this->upstreams = Git::upstreams();
		return $this;
	}

	public function labelFor($item)
	{
		$text = $item['value'] ? '* ' : '  ';
		$text .= $item['label'];

		if ($upstream = $this->upstreams[$item['label']] ?? false) {
			$text .= " -> {$upstream}";
		}

		return $text;
	}

	public function colorFor($item)
	{
		if ($item['value']) {
			return [SGR::COLOR_FG_WHITE_BRIGHT, SGR::COLOR_BG_BLACK];
		}
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function selectedBranch()
	{
		if ($item = $this->items[$this->index] ?? false) {
			return $item['label'];
		}
	}

	public function keyPress($key)
	{
		if ($key == 'Esc' || $key == 'F4' || $key == 'Tab') {
			gui()->action('status');
		}

		elseif ($key == 'Enter') {
			if ($item = $this->getItem($this->index)) {
				$branch = trim($item['label']);
				gui()->process("git checkout {$branch}");
				$this->reload();
				App::render();
			}
		}

		elseif ($key == 'F3')  {
			$branch = $this->selectedBranch();
			$current = $this->currentBranch;
			if ($branch && $current && $branch !== $current) {
				gui()->actionMerge($branch);
			}
		}

		elseif ($key == 'F5')  {
			gui()->actionPull();
		}

		elseif ($key == 'F9')  {
			gui()->actionPush();
		}

		elseif ($key == 'Ctrl+Left' || $key == 'Left')  {
			gui()->action('index');
			w('index')->diffFile();
		}

		elseif ($key == 'Ctrl+Right' || $key == 'Right' || $key == 'Ctrl+Down')  {
			gui()->action('remote');
		}

		elseif ($key == 'Ins')  {
			gui()->toStatus();
			gui()->action('dialog');
			w('dialog')->prompt('New branch name:', ''
				,function($dialog) {
					if ($name = trim($dialog->inputText)) {
						$name = preg_replace('{[^a-z0-9_]}i', '_', $name);
						gui()->process("git checkout -b {$name}");
						w('branches')->reload();
						gui()->toStatus();
					}
				}

				,function($dialog) {
					gui()->action('status');
				}
			);
		}

		elseif ($key == 'Del')  {
			if ($item = $this->items[$this->index] ?? false) {
				$branch = trim($item['label']);
				if ($branch == 'master') {
					return;
				}
		
				gui()->action('dialog');
				w('dialog')->confirm("Delete branch {$branch} (Enter, Esc)?"
					,function($dialog) use($branch) {
						gui()->action('status');
						gui()->process("git branch -D {$branch}");
						GitGui::reload();
					}

					,function($dialog) {
						gui()->action('status');
					}
				);
			}
		}
		else {
			return parent::keyPress($key);
		}
	}

	public function help()
	{
		$help = [
			'F3' => 'Merge',
			'F5' => 'Pull',
			'F9' => 'Push',
			'Ins' => 'Create new branch',
			'Del' => 'Delete branch',
		];
		return $help;
	}
}