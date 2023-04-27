<?php

namespace Guanfangge\RcuHotels\Control;

use Guanfangge\RcuHotels\Contracts\Rcu;
use GuzzleHttp\Client;

class RcuMiuLink implements Rcu
{
    public function getDevice($roomId,$hotelId,$deviceType)
    {
        $aParams =[
            "data"=> "sendmsg",
            "eroom"=> $roomId,
            "efloor"=> "0",
            "esta"=>"-1",
            "hotelnum"=> $hotelId,
            "edev"=> $this->getType($deviceType),
            "devname"=>""
        ];
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8'],
            ]
        );

        $sHttpRes = $httpClient->post("https://www.miulink.com/smarthotel/Equips", ['body' => http_build_query($aParams)])->getBody()->getContents();
        $response = json_decode($sHttpRes,true);
        $collect = collect($response);
        $collect = $collect->filter(function ($item){
            return $item['devicetype_keynum']==0;
        })->values();
        $response  = $collect->map(function ($item){
            $item['name'] = $item['title'];
            $item['id'] = hexdec($item['number']);
            $item['type'] = $item['devicetype_name'] =="灯光"? "LIGHT":$item['devicetype_name'];
            $item['type'] = $item['devicetype_name'] =="换气扇"? "LIGHT":$item['type'];
            $item['type'] =  strpos($item['title'],"窗帘")===0 ? "CURTAIN" :$item['type'];
            $item['STATE_ON'] = "STATE_ON";
            if($item['type']=="CURTAIN"){
                $item['state'] = $item['state']==="关闭" ? 0:2;
            }
            if($item['type']=="LIGHT"){
                $item['state'] = $item['state']==="关闭" ? 0:100;
            }
            return $item;
        });
        $data['Data'] = $response;
        return $data;
    }

    public function setDevice($roomId,$hotelId,$deviceId,$deviceState,$action="",$deviceAttr="",$attrValue="")
    {
        //模式
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8'],
            ]
        );
        $ff= "";
        for ($i=0;$i<66;$i++){
            if($i== intval($deviceId)){
                $ff .=  $this->getState($deviceState);
            }else{
                $ff .= "FF";
            }
        }
        $parmas = "msg=B1${hotelId}${roomId}FFFF00A701AFFFFFFFFFFEBEEA48${ff}B1B1AAAA0D0A";
        $aParams = ["hotelnum"=>$hotelId,"data"=>$parmas];
        $sHttpRes = $httpClient->post("https://www.miulink.com/smarthotel/Control", ['body' => http_build_query($aParams)])->getBody()->getContents();
        return json_decode($sHttpRes);
    }

    public function getType($type)
    {
        $types = collect([
            ""=>"所有设备",
            "LIGHT"=>"灯光"
        ]);
        return $types->get($type);
    }

    public function getState($state)
    {
        $states = collect([
            "STATE_ON"=>"01",
            "STATE_OFF"=>"00",
            "STATE_STOP"=>"04",
        ]);
        return $states->get($state);
    }
}