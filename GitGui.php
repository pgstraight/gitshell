<?php

class GitGui
{
	public static $instance = false;
	public $action = 'status';
	public $currentFile = false;

	public function run()
	{
		self::$instance = $this;

		App::$widgets['title'] = (new WidgetText)->setText(Git::$title);
		App::$widgets['status'] = (new WidgetStatus)->setTitle('Files')->reload();
		App::$widgets['index'] = (new WidgetIndex)->setTitle('Index')->reload();
		App::$widgets['branches'] = (new WidgetBranches)->setTitle('Branches')->reload();
		App::$widgets['remote'] = (new WidgetRemote)->setTitle('Remote branches')->reload();
		App::$widgets['diff'] = (new WidgetDiff)->setTitle('Diff');
		App::$widgets['process'] = (new WidgetProcess)->setTitle('Process')->disable();
		App::$widgets['dialog'] = new WidgetDialog;
		App::$widgets['help'] = new WidgetHelp;


		$this->resize();
		App::focus($this->action);
		$this->idle();
	}

	public static function reload()
	{
		App::$widgets['status']->reload();
		App::$widgets['index']->reload();
		App::$widgets['branches']->reload();
		App::$widgets['remote']->reload();
		App::render();
	}

	public function resize()
	{
		$w = App::$w;
		$w3 = $w - 50;
		$w2 = floor($w3 / 2) + 12;
		$h = App::$h;
		$h2 = floor($h / 2);

		w('title')->setPos(1,1);
		w('status')->setSize(1,2, $w2, $h2);
		w('index')->setSize($w2 + 1,2, $w3 - 1, $h2);
		w('branches')->setSize($w3,2, $w, $h2);
		w('remote')->setSize($w3,$h2 + 1, $w, $h - 4);
		w('diff')->setSize(1, $h2 + 1, $w3 - 1, $h - 4);
		w('process')->setSize(1, $h2 + 1, $w3 - 1, $h - 4);
		w('dialog')->setSize(1, $h - 3, $w, $h - 1);
		w('help')->setPos(1,$h);

		cls();
		App::render();
	}

	public function activeWidget()
	{
		return App::$widgets[$this->action];
	}

	public function action($action)
	{
		w($action)->reload();
		$this->action = $action;
		App::focus($action);
		App::render();
		return $this;
	}

	public function process($cmd)
	{
		w('diff')->disable();
		w('process')->clear()->setTitle($cmd)->enable();
		$this->action('process');

		$descriptors = array(
		    0 => array("pipe", "r"),  // STDIN
		    1 => array("pipe", "w"),  // STDOUT
		    2 => array("pipe", "w")   // STDERR
		);
		$proc = proc_open($cmd, $descriptors, $pipes);

		while ($s = fgets($pipes[1])) {
			App::$widgets['process']->addLine(rtrim($s))->scrollEnd();
		}

		while ($s = fgets($pipes[2])) {
			App::$widgets['process']->addLine(rtrim($s))->scrollEnd();
		}

		proc_close($proc);
	}

	public function actionCommit()
	{
			$this->action('dialog');
			w('dialog')->prompt('Commit message:', ''
				,function($dialog)  {
					$mess = trim($dialog->inputText);
					$mess = str_replace('"', '~', $mess);
					gui()->process('git commit -m "' . $mess . '"');
					GitGui::reload();
				}

				,function($dialog) {
					gui()->action = 'status';
					App::focus('status');
					GitGui::reload();
				}
			);
	}

	public function actionMerge($branch)
	{
		$current = Git::currentBranch();
		if ($current != $branch) {
			gui()->action('dialog');
			w('dialog')->confirm("Merge {$branch} to {$current} (Enter, Esc)?"
				,function($dialog) use($branch) {
					$this->process('git merge --no-edit ' . $branch);
					GitGui::reload();
				}
	
				,function($dialog) {
					gui()->action('status');
				}
			);
		}
	}

	public function actionPush()
	{
		gui()->action('dialog');
		w('dialog')->confirm('Git push (Enter, Esc)?'
			,function($dialog) {
				$branch = Git::currentBranch();
				if ($upstream = Git::upstreamFor($branch)) {
					$this->process('git push');
				} else {
					$this->process('git push --set-upstream origin ' . $branch);
				}
				GitGui::reload();
			}

			,function($dialog) {
				gui()->action('status');
			}
		);
	}

	public function actionPull()
	{
		$this->action('dialog');
		w('dialog')->confirm('Git pull (Enter, Esc)?'
			,function($dialog) {
				gui()->action('status');
				$this->process('git pull --no-edit');
			}

			,function($dialog) {
				gui()->action('status');
			}
		);
	}

	public function toStatus()
	{
		w('process')->disable();
		w('diff')->enable();
		$this->action('status');
	}

	public function idle()
	{
		$self = $this;
		while (true) {
			$key = getKey();//w('title')->setText($key . '     ');App::render();
			if ($key == 'Esc' && ($this->action == 'status' || $this->action == 'index' || $this->action == 'branches' || $this->action == 'remote')) {
				cls();
				return;
			}
			elseif (($key == 'Ctrl+Down' || $key == 'Ctrl+Up' || $key == 'Ctrl+Home' || $key == 'Ctrl+End' || $key == 'Ctrl+PageUp' || $key == 'Ctrl+PageDown') && ($this->action == 'status'))  {
				w('diff')->keyPress($key);
			}
			else {
				App::$widgets[$this->action]->keyPress($key);
			}
		}
	}
}


function gui()
{
	return GitGui::$instance;
}
