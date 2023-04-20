<?php
/**
 * Created by PhpStorm.
 * User: guanfangge
 * Date: 4/20/23
 * Time: 10:01 AM
 */


require_once __DIR__ . '/vendor/autoload.php';

use \Guanfangge\RcuHotels\Handler\Rcu;
use \Guanfangge\RcuHotels\Control\RcuLianDong;
use \Guanfangge\RcuHotels\Control\RcuThinke;

$rcu = new Rcu();

$config  =  [

];
$think = new  RcuThinke($config);
#$think = new RcuLianDong();
$data = [
    "roomId"=>"8403",
    "hotelId"=>"33",
    "deviceType"=>""
];
$response = $rcu->getDevice($think,$data);
var_dump($response);