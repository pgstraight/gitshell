<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetRemote extends WidgetList
{
	public function reload()
	{
		$this->items = [];
		foreach(Git::remoteBranches() as $name) {
			$this->items[] = [
				'label' => $name,
			];
		}
		$this->index = 0;
		$this->scroll = 0;
		return $this;
	}

	public function labelFor($item)
	{
		return $item['label'];
	}

	public function colorFor($item)
	{
		return [SGR::COLOR_FG_WHITE, SGR::COLOR_BG_BLACK];
	}

	public function keyPress($key)
	{
		if ($key == 'Esc' || $key == 'F4' || $key == 'Tab') {
			gui()->action('status');
		}

		elseif ($key == 'Enter') {
			if ($item = $this->getItem($this->index)) {
				$branch = trim($item['label']);
				gui()->process("git fetch origin {$branch}");
				$this->reload();
				w('branches')->reload();
				w('status')->reload();
				w('index')->reload();
				App::render();
			}
		}

		elseif ($key == 'F5')  {
			gui()->actionPull();
		}

		elseif ($key == 'F9')  {
			gui()->actionPush();
		}

		elseif ($key == 'Ctrl+Left' || $key == 'Left' || $key == 'Ctrl+Up')  {
			gui()->action('branches');
		}

		elseif ($key == 'Del')  {
			if ($item = $this->items[$this->index] ?? false) {
				$branch = trim($item['label']);
				$origin = false;
				if (preg_match('{^(.+?)/(.+)$}', $branch, $m)) {
					$origin = trim($m[1]);
					$branch = trim($m[2]);
				}

				if (!$origin || $branch == 'master') {
					return;
				}
		
				gui()->action('dialog');
				w('dialog')->confirm("Delete remote branch {$origin}/{$branch} (Enter, Esc)?"
					,function($dialog) use($origin, $branch) {
						gui()->action('remote');
						gui()->process("git push {$origin} --delete {$branch}");
						GitGui::reload();
					}

					,function($dialog) {
						gui()->action('remote');
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
			'Enter' => 'Fetch',
			'Del' => 'Delete',
		];
		return $help;
	}
}