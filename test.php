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

$config  =  [
    "miulink"=>[]
];
$rcu = new Rcu("miulink",$config);
#$think = new  RcuThinke($config);
$data = [
    "1401",
    "05B4",
    ""
];
$think = new RcuMiuLink();

//$config = [
//    "username"=>"18551725735",
//    "password"=>"a123456",
//    "appid"=>"bc0f99f4124f9f85578a5b2703ffa263",
//    "appkey"=>"58fd48b2d13b7e48",
//];
//$rcu = new  RcuGeekLink($config);
//$re = $rcu->getDevice();
//
//var_dump($re);

//$response = $rcu->getDevice($data);
//var_dump($response);

