<?php

use \Bramus\Ansi\Ansi;
use \Bramus\Ansi\Writers\StreamWriter;
use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

class App
{
	public static $ansi;
	public static $w;
	public static $h;
	public static $seqs;
	public static $keys;
	public static $widgets = [];

	static function run($call)
	{
		system("stty -icanon");
		self::$ansi = new Ansi(new StreamWriter('php://stdout'));
		self::$w = exec('tput cols');
		self::$h = exec('tput lines');
		self::$seqs = require('config-seqs.php');
		self::$keys = require('config-keys.php');
		call_user_func($call);
	}

	static function render()
	{
		foreach(self::$widgets as $name => $widget) {
			if (!$widget->disabled) {
				$widget->render();
			}
		}
	}

	static function focus($w)
	{
		foreach(self::$widgets as $name => $widget) {
			if ($w == $name) {
				$widget->focus();
			} else {
				$widget->blur();
			}
		}
		self::render();
	}
}

function a()
{
	return App::$ansi;
}

function cls()
{
	return App::$ansi->eraseDisplay();
}

function t($x, $y, $s)
{
	a()->cursorPosition($y, $x)->text($s);
}

function frame($x1, $y1, $x2, $y2)
{
	for ($x = $x1 + 1; $x < $x2; $x++) {
		t($x, $y1, '─');
	}
	for ($x = $x1 + 1; $x < $x2; $x++) {
		t($x, $y2, '─');
	}
	for ($y = $y1 + 1; $y < $y2; $y++) {
		t($x1, $y, '│');
	}
	for ($y = $y1 + 1; $y < $y2; $y++) {
		t($x2, $y, '│');
	}
	t($x1, $y1, '┌');
	t($x2, $y1, '┐');
	t($x1, $y2, '└');
	t($x2, $y2, '┘');
}

function ctoi()
{
	a()->color([SGR::COLOR_FG_YELLOW_BRIGHT, SGR::COLOR_BG_BLACK]);
	t(App::$w - 30, 1, '                    ');
	a()->cursorPosition(1, App::$w - 30);
}

function getOneKey()
{
	ctoi();
	$c = fread(STDIN, 1); 
	ctoi();
	return ord($c);
}

function getKeySeq()
{
	$c = getOneKey();
	$m = trim($c);
	while(isset(App::$seqs[$m])) {
		$c = getOneKey();
		$m .= ',' . trim($c);
	}
	return $m;
}


function getKey()
{
	$seq = getKeySeq();
	if (strpos($seq, ',') === false) {
		$key = (int)($seq);
		if ($key == 10) return "Enter";
		if ($key == 9) return "Tab";
		if ($key == 127 || $key == 8) return "Bs";
		if ($key >= 32) return chr($key);
		return "*{$key}";
	}
	return isset(App::$keys[$seq]) ? App::$keys[$seq] : $seq;
}

function _log($s)
{
	$s = trim($s);
	$f = fopen('./.log', 'a');
	fputs($f, "{$s}\n");
	fclose($f);
}

function w($name)
{
	return App::$widgets[$name];
}


/*
┌─┐
│ │
└─┘
*/