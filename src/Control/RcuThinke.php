<?php

namespace Guanfangge\RcuHotels\Control;


use Guanfangge\RcuHotels\Contracts\Rcu;
use GuzzleHttp\Client;

class RcuThinke implements Rcu
{
    public $thinker;
    public $url;
    public function __construct($config){
        $this->thinker = $config['thinker'];
        $this->url = $config['url'];
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
    public function updateMode($roomId,$hotelId,$mode=1){
        //模式名称
        $sence = self::getStatus($hotelId,$roomId);
        if($sence){
            $name = "无模式";
            foreach ($sence as &$val){
                if($val['type']=='SCENE'){
                    //当前模式
                    if($val['state']==1)$name = $val['name'];
                    if($val['name']=="插卡模式") $inCard = $val['id'];
                    if($val['name']=="拔卡模式") $outCard = $val['id'];
                }
            }
            if(($mode==1 && ($name == "拔卡模式"||$name == '无模式')) || $mode==2){
                $deviceId = $mode==1? $inCard : $outCard;
                $deviceState = "STATE_ON";
                $config = config('rcu')['defaults'];
                $action = "SCENECONTROL";
                $deviceAttr = "";
                $attrValue = "";
                $date = date("YmdHis", time());
                $deviceAction = "ACTION_TO";
                $sign = sha1("{$config['thinker']}Action={$action}DeviceAction={$deviceAction}DeviceAttribute={$deviceAttr}DeviceAttributeValue={$attrValue}DeviceId={$deviceId}DeviceState={$deviceState}HotelId={$hotelId}RoomId={$roomId}TimeStamp={$date}{$config['thinker']}");
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
                return $httpClient->post($config['url'], ['json' => $aParams])->getBody()->getContents();
            }
            return false;
        }
    }

    //rcu 获取模式
    public function getStatus($hotelId=33,$roomId=8820,$deviceType='SCENE'){
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
        $res = json_decode($sHttpRes,true);
        if($res['ErrCode']==200){
            return $res['Data'];
        }
        return false;
    }

}