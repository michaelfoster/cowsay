#!/usr/bin/php
<?php

define('COWSAY_VERSION',	'1.0');
define('COWSAY_PROGNAME',	basename(__FILE__));
define('COWSAY_COWPATH',	getenv('COWPATH') ?
					getenv('COWPATH')
				:
					__DIR__ . '/cows' . (is_dir('/usr/share/cowsay/cows') ? ':/usr/share/cowsay/cows' : '')
				);

$opts_default = array(
	'e' => 'oo',
	'f'=> 'default.cow',
	'T' => '  ',
	'W' => 40
);

$opts = getopt($shortoptions = 'bde:f:ghlLnNpstT:wW:y');

// find the start of the message (after getopt)
$index = 1;
foreach($opts as $key => $val) {
	$index++;
	if($val !== false)
		$index++;
}

$after_args = implode(' ', array_slice($argv, $index));

$opts = array_merge($opts_default, $opts);

// Make $opts easier to use
foreach (str_split($shortoptions) as $c) {
	if ($c == ':')
		continue;
	if (isset($opts[$c]) && $opts[$c] === false)
		$opts[$c] = true;
	if (!isset($opts[$c]))
		$opts[$c] = false;
}


if ($opts['h'])
	die(display_usage());
if ($opts['l'])
	die(list_cowfiles());

$borg = $opts['b'];
$dead = $opts['d'];
$greedy = $opts['g'];
$paranoid = $opts['p'];
$stoned = $opts['s'];
$tired = $opts['t'];
$wired = $opts['w'];
$young = $opts['y'];
$eyes = substr($opts['e'], 0, 2);
$tongue = substr($opts['T'], 0, 2);
$cowfile = $opts['f'];
$wrap = $opts['W'];

// Get input
if($after_args != '')
	$message = $after_args;
else
	$message = trim(stream_get_contents(STDIN));

if ($opts['n']) {
	$message = wordwrap($message, $wrap);
	
} else {
	$message = wordwrap(str_replace("\n", ' ', $message), $wrap);
}
$message = explode("\n", $message);

construct_face();

$balloon_lines = construct_balloon($message);
$cowpath = get_cow_path($cowfile);
$cow = exec_cow($cowpath);

echo implode("\n", $balloon_lines) . "\n" . $cow . "\n";

function maxlength($input) {
	$m = -1;
	foreach ($input as $i) {
		$l = strlen($i);
		if ($l > $m)
			$m = $l;
	}
	return $m;
}

function construct_balloon(&$message) {
	global $format, $border, $thoughts;
	
	$max = maxlength($message);
	$max2 = $max + 2;
	$format = "%s %-${max}s %s";
	$balloon_lines = array();
	
	$border = array(); // up-left, up-right, down-left, down-right, left, right
	
	$thoughts = '\\';
	
	if (count($message) < 2)
		$border = array('<', '>');
	else
		$border = array('/', '\\', '\\', '/', '|', '|');
	
	$balloon_lines[] = ' ' . str_repeat('_', $max2);
	$balloon_lines[] = sprintf($format, $border[0], $message[0], $border[1]);
	
	if (count($message) >= 2) {
		foreach (array_slice($message, 1, -1) as $line) {
			$balloon_lines[] = sprintf($format, $border[4], $line, $border[5]);
		}
		
		$balloon_lines[] = sprintf($format, $border[2], end($message), $border[3]);
	}
	
	$balloon_lines[] =  ' ' . str_repeat('-', $max2) . ' ';
	
	return $balloon_lines;	
}

function exec_cow($cowpath) {
	global $thoughts, $eyes, $tongue, $cowfile;
	
	if (!$cow = @file_get_contents($cowpath))
		die(COWSAY_PROGNAME . ": Could not open {$cowpath} for reading!\n");
	
	// Attempt to the convert Perl syntax into PHP
	$cow = preg_replace('/\$the_cow\s*=\s*<<\s*("|)EOC\\1;/', '$the_cow = <<< EOC', $cow);
	
	eval($cow . ';');
	
	if (!isset($the_cow))
		die(COWSAY_PROGNAME . ": {$cowfile} did not seem to do anything!\n");
	
	return $the_cow;
}

function get_cow_path($cowfile) {
	$cowpath = false;
	if (strpos($cowfile, '/') !== false && is_file($cowfile)) {
		$cowpath = $cowfile;
	} else {
		$paths = explode(':', COWSAY_COWPATH);
		foreach ($paths as $path) {
			if (is_file($path . '/' . $cowfile))
				$cowpath = $path . '/' . $cowfile;
			elseif (is_file($path . '/' . $cowfile . '.cow'))
				$cowpath = $path . '/' . $cowfile . '.cow';
			else
				continue;
			break;
		}
	}
	
	if ($cowpath === false)
		die(COWSAY_PROGNAME . ": Could not find {$cowfile} cowfile!\n");
	
	return $cowpath;
}

function construct_face() {
	global $borg, $greedy, $paranoid, $tired, $wired, $young, $dead, $stoned, $eyes, $tongue;
	
	if ($borg)
		$eyes = "==";
	if ($greedy)
		$eyes = "\$\$";
	if ($paranoid)
		$eyes = "@@";
	if ($tired)
		$eyes = "--";
	if ($wired)
		$eyes = "OO";
	if ($young)
		$eyes = "..";
	if ($dead) {
		$eyes = "xx";
		$tongue = "U ";
	}
	if ($stoned) {
		$eyes = "**";
		$tongue = "U ";
	}
}

function display_usage() {
	echo	"php-cowsay version " . COWSAY_VERSION . ", (c) 2012 Michael Foster\n" .
		"Usage: " . COWSAY_PROGNAME . " [-bdgpstwy] [-h] [-e eyes] [-f cowfile]\n" .
		"          [-l] [-n] [-T tongue] [-W wrapcolumn] [message]\n";
}

function list_cowfiles() {
	$paths = explode(':', COWSAY_COWPATH);
	$cows = array();
	
	foreach ($paths as $path) {
		echo "Cow files in $path:\n";
		
		if (($dir = @opendir($path)) === false)
			die("Cannot open $path\n");
		
		while (($file = readdir($dir)) !== false) {
			if (($cow = preg_replace('/\.cow$/', '', $file)) != $file)
				$cows[] = $cow;
		}
		closedir($dir);
		
		echo wordwrap(implode(' ', $cows), 75) . "\n";
	}
}

