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
            "eroom"=> "8000",
            "efloor"=> "0",
            "esta"=>"-1",
            "hotelnum"=> "0542",
            "edev"=>"所有设备",
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
        $response  = $collect->map(function ($item){
            $item['name'] = $item['title'];
            $item['id'] = $item['number'];
            $item['type'] = $item['devicetype_name'] =="灯光"? "LIGHT":$item['devicetype_name'];
            $item['STATE_ON'] = "STATE_ON";
            $item['state'] = $item['state']==="关闭" ? 0:100;
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
                $ff .= $deviceState=== "STATE_ON" ? "01":"00";
            }else{
                $ff .= "FF";
            }
        }
        $parmas = "msg=B105428000FFFF00A701AFFFFFFFFFFEBEEA48${ff}B1B1AAAA0D0A";
        $aParams = ["hotelnum"=>"0542","data"=>$parmas];
        $sHttpRes = $httpClient->post("https://www.miulink.com/smarthotel/Control", ['body' => http_build_query($aParams)])->getBody()->getContents();
        return json_decode($sHttpRes);
    }

}