<?php 

require_once '../modelos/Test.php';

$obj = new Test();
$r = $obj->get();

var_dump($r);
