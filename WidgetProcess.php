<?php

class WidgetProcess extends WidgetView
{
	public function keyPress($key)
	{
		if ($key == 'Esc' || $key == 'Enter' || $key == 'Tab' || $key == 'Up') {
			$this->disable();
			w('diff')->enable();
			gui()->action('status');
			return $this;
		}

		elseif ($key == 'F2')  {
			gui()->actionCommit();
		}

		elseif ($key == 'F4') {
			gui()->action('branches');
		}

		elseif ($key == 'F5')  {
			gui()->actionPull();
		}

		elseif ($key == 'F9')  {
			gui()->actionPush();
		}

		return parent::keyPress(substr($key, 5));
	}

}