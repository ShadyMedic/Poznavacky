<?php
mb_internal_encoding('UTF-8');

function autoloader($name)
{
    if (preg_match('/Controller$/', $name)){ require 'Controllers/'.$name.'.php'; }
    else { require 'Models/'.$name.'.php'; }
}

spl_autoload_register('autoloader');

$rooter = new RooterController();
$rooter->process(array($_SERVER['REQUEST_URI']));
$rooter->displayView();