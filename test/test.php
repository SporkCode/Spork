#!/usr/bin/php
<?php
require_once 'PHPUnit/TextUI/Command.php';
require_once 'PHPUnit/Util/Getopt.php';
require_once 'PHPUnit/Util/Configuration.php';
require_once 'PHPUnit/Util/XML.php';
require_once 'PHPUnit/Util/Fileloader.php';

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');

PHPUnit_TextUI_Command::main();