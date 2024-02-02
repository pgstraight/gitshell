<?php

class Git
{
	public static $dir = false;
	public static $title = false;
	public static $statuses = [];

	public static function init()
	{
		$dir = getcwd();
		$dir = rtrim($dir, '/');
		while (strlen($dir) > 3) {
			if (is_file("{$dir}/.git/config")) {
				self::$dir = $dir;
				break;
			}
			$dir = dirname($dir);
			$dir = rtrim($dir, '/');
		}
		if (!self::$dir) {
			print "fatal: not a git repository (or any of the parent directories): .git\n";
			die;
		}

		self::$title = self::$dir;

	}

	public static function status($what = 0)
	{
		$src = shell_exec('git status --porcelain -u');
		$files = [];
		foreach(explode("\n", $src) as $line) {
			if (strlen($line) > 3 && $line[2] == ' ') {
				$index = $line[0];
				$work = $line[1];
				self::$statuses[self::$dir . '/' . trim(substr($line, 3))] = "{$index}{$work}";
				if ($what == 1 && $index != '?' && $index != ' ') {
					continue;
				}
				if ($what == 2 && ($index == '?' || $index == ' ')) {
					continue;
				}
				if ($path = trim(substr($line, 3))) {
					$files[$path] = "{$index}{$work}";
				}
			}
		}
		return $files;
	}

	public static function getFileStatus($path)
	{
		if (isset(self::$statuses[$path])) {
			return self::$statuses[$path];
		}
		return false;
	}

	public static function branches()
	{
		$src = shell_exec('git branch');
		$items = [];
		foreach(explode("\n", $src) as $line) {
			if ($line = trim($line)) {
				if ($line[0] == '*') {
					$line = trim(substr($line, 1));
					$items[$line] = true;
				} else {
					$items[$line] = false;
				}
			}
		}
		return $items;
	}

	public static function upstreams()
	{
		$src = shell_exec('git for-each-ref --format="%(refname:short):::%(upstream:short)" refs/heads');
		$items = [];
		foreach(explode("\n", $src) as $line) {
			if ($line = trim($line)) {
				if (preg_match('{^(.+):::(.+)$}', $line, $m)) {
					$branch = trim($m[1]);
					$upstream = trim($m[2]);
					if ($branch && $upstream) {
						$items[$branch] = $upstream;
					}
				}
			}
		}
		return $items;
	}

	public static function remoteBranches()
	{
		$src = shell_exec('git branch -r');
		$items = [];
		foreach(explode("\n", $src) as $line) {
			if ($line = trim($line)) {
				if (strpos($line, '->') !== false) {
					continue;
				}
				$items[$line] = $line;
			}
		}
		return array_values($items);
	}

	public static function currentBranch()
	{
		foreach(self::branches() as $branch => $current) {
			if ($current) {
				return $branch;
			}
		}
	}

	public static function upstreamFor($branch)
	{
		foreach(self::upstreams() as $b => $upstream) {
			if ($b === $branch) {
				return $upstream;
			}
		}
		return false;
	}

}
