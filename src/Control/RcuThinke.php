<?php

namespace Guanfangge\RcuHotels\Control;


use Guanfangge\RcuHotels\Contracts\Rcu;
use GuzzleHttp\Client;

class RcuThinke implements Rcu
{
    public $thinker;
    public $url;
    public function __construct($config){
        $this->thinker = $config['defaults']['thinker'];
        $this->url = $config['defaults']['url'];
    }

    public function getDevice($roomId="",$hotelId="",$deviceType="")
    {
        $date = date("YmdHis", time());
        $action = 'DEVICELIST';
        $sign = sha1("{$this->thinker}Action={$action}DeviceType={$deviceType}HotelId={$hotelId}RoomId={$roomId}TimeStamp={$date}{$this->thinker}");
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ['Sign' => $sign, 'Content-Type' => 'application/json'],
            ]
        );
        $aParams['Action'] = $action;
        $aParams['HotelId'] = $hotelId;
        $aParams['RoomId'] = $roomId;
        $aParams['TimeStamp'] = $date;
        //$aParams['DeviceId'] = $deviceId;
        $aParams['DeviceType'] = $deviceType;
        $sHttpRes = $httpClient->post($this->url, ['json' => $aParams])->getBody()->getContents();
        return json_decode($sHttpRes);
    }

    public function setDevice($roomId,$hotelId,$deviceId="",$deviceState="",$action="",$deviceAttr,$attrValue)
    {
        $date = date("YmdHis", time());
        $deviceAction = "ACTION_TO";
        $sign = sha1("{$this->thinker}Action={$action}DeviceAction={$deviceAction}DeviceAttribute={$deviceAttr}DeviceAttributeValue={$attrValue}DeviceId={$deviceId}DeviceState={$deviceState}HotelId={$hotelId}RoomId={$roomId}TimeStamp={$date}{$this->thinker}");
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ['Sign' => $sign, 'Content-Type' => 'application/json'],
            ]
        );
        $aParams['Action'] = $action;
        $aParams['HotelId'] = $hotelId;
        $aParams['RoomId'] = $roomId;
        $aParams['TimeStamp'] = $date;
        $aParams['DeviceId'] = $deviceId;
        $aParams['DeviceState'] = $deviceState;
        $aParams['DeviceAction'] = $deviceAction;
        $aParams['DeviceAttribute'] = $deviceAttr;
        $aParams['DeviceAttributeValue'] = $attrValue;

        $sHttpRes = $httpClient->post($this->url, ['json' => $aParams])->getBody()->getContents();
        return json_decode($sHttpRes);
    }
}