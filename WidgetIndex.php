<?php

use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class WidgetIndex extends WidgetStatus
{
	protected function statusWhat()
	{
		return 2;
	}

	public function keyPress($key)
	{
		if ($key == 'Enter') {
			if ($file = $this->currentFile()) {
				shell_exec("git reset {$file}");
				GitGui::reload();
			}
		}
		elseif ($key == 'Tab' || $key == 'Ctrl+Left' || $key == 'Left')  {
			gui()->action('status');
		}
		elseif ($key == 'Ctrl+Right' || $key == 'Right') {
			gui()->action('branches');
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

	public function afterRenderItem($x, $y, $item, $selected, $text)
	{
	}

	public function help()
	{
		return [
			'F2' => 'Commit',
			'F5' => 'Pull',
			'F9' => 'Push',
		];
	}
}