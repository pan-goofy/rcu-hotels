<?php
/**
 * Created by PhpStorm.
 * User: guanfangge
 * Date: 4/20/23
 * Time: 10:01 AM
 */


require_once __DIR__ . '/vendor/autoload.php';

use \Guanfangge\RcuHotels\Handler\Rcu;
use \Guanfangge\RcuHotels\Control\RcuMiuLink;
use \Guanfangge\RcuHotels\Control\RcuThinke;
use \Guanfangge\RcuHotels\Control\RcuGeekLink;
$rcu = new Rcu();

$config  =  [

];
#$think = new  RcuThinke($config);
$think = new RcuMiuLink();
$data = [
    "8403",
    "33",
    ""
];
$response = $rcu->getDevice($think,$data);
var_dump($response);