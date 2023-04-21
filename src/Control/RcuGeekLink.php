<?php

namespace Guanfangge\RcuHotels\Control;


use Guanfangge\RcuHotels\Contracts\Rcu;
use GuzzleHttp\Client;

class RcuGeekLink implements Rcu
{
    public function getDevice($roomId="",$hotelId="",$deviceType="")
    {
       return  [];
    }

    public function setDevice($roomId,$hotelId,$deviceId="",$deviceState="",$action="",$deviceAttr,$attrValue)
    {
       return [];
    }
}