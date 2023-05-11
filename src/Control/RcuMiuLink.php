<?php

namespace Guanfangge\RcuHotels\Control;

use Guanfangge\RcuHotels\Contracts\Rcu;
use GuzzleHttp\Client;
use function Safe\json_decode;
use function Safe\mb_split;

class RcuMiuLink implements Rcu
{
    public function getDevice($roomId,$hotelId,$deviceType)
    {
        $mode =  collect($this->getMode($roomId,$hotelId));
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
            return $item['devicetype_keynum']==0 && strpos($item['title'],"继电器") ==false
                && strpos($item['title'],"联") ==false;
        })->values();
        $response  = $collect->map(function ($item){
            $item['name'] = $item['title'];
            $item['id'] = hexdec($item['number']);
            $item['type'] = $item['devicetype_name'] =="灯光"? "LIGHT":$item['devicetype_name'];
            $item['type'] = $item['devicetype_name'] =="换气扇"? "LIGHT":$item['type'];
            $item['type'] =  strpos($item['title'],"窗帘") !==false ? "CURTAIN" :$item['type'];
            $item['STATE_ON'] = "STATE_ON";
            if($item['type']=="CURTAIN"){
                $item['state'] = $item['state']==="关闭" ? 0:2;
            }
            if($item['type']=="LIGHT"){
                $item['state'] = $item['state']==="关闭" ? 0:100;
            }
            return $item;
        });
        $response = $response->concat($mode);

        $data['Data'] = $response;
        return $data;
    }

    public function setDevice($roomId,$hotelId,$deviceId,$deviceState,$action="",$deviceAttr="",$attrValue="")
    {
        //模式控制
        if($action=="SCENECONTROL"){
            return $this->setMode($roomId,$hotelId,$deviceId,$deviceState);
        }
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

    public function getMode($roomId,$hotelId)
    {
        //模式
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8'],
            ]
        );
        $data = "FLMD,${hotelId},${roomId}";
        $sHttpRes = $httpClient->post("https://www.miulink.com/smarthotelserver", ['body' => $data])->getBody()->getContents();
        $arr  =  mb_split("<00>",$sHttpRes);
        $str = [];
        foreach ($arr as $key=>$val){
            $split = mb_split("<",$val);
            $str[$key]['devices']  =[];
            if(count($split)>2){

                $device = mb_split(",",$split[1]);
                $der = [];
                $ds =0;
                foreach ($device  as $k=>$v){
                    if($v=="关灯"||$v=="开灯"){
                        $der[$k]=$v;
                        $ds++;
                        if ($ds % 2 == 1) {
                            $der[] = array(
                                "id" => $v,
                                "state" => $arr[$key - 1]
                            );
                        }
                    }
                    if(ctype_xdigit($v)){
                        $der[$k]=$v;
                        $ds++;
                    }
                }
                $str[$key]['name'] =  str_replace('MD01>', '', $split[0]);
                $str[$key]['id'] =  str_replace('MD01>', '', $split[0]);
                $str[$key]['devices'] = $der;
                $str[$key]['type'] = "SCENE";
                $str[$key]['state'] = end($split);
            }
        }
        return $str;
    }

    public function setMode($roomId,$hotelId,$deviceId,$status){
        $status = $status =="STATE_OFF" ? "0":"1";
        //模式
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8'],
            ]
        );
        $data = "FLRS,${hotelId},${roomId},${deviceId},${status}";
        $res = $httpClient->post("https://www.miulink.com/smarthotelserver", ['body' => $data])->getBody()->getContents();
        //关闭打开模式对应设备
        $mode = $this->getMode($roomId,$hotelId);
        $device = collect($mode)->where("name",$deviceId)->first();
        if(empty($device['devices'])){
            return $res;
        }
        $devices  = collect($device['devices'])->values();
        $newDevice = array();
        foreach ($devices as $key => $value) {
            // 如果当前元素的键为偶数，则创建一个新的关联数组
            if ($key % 2 == 1) {
                $item = array(
                    "id" => $devices[$key - 1],
                    "state" => $value
                );
                // 将新的关联数组添加到新数组中
                $newDevice[] = $item;
            }
        }
        $ff= "";
        for ($i=0;$i<66;$i++){
            for ($j=0;$j<count($newDevice);$j++){
                if(hexdec($newDevice[$j]["id"]) == $i){
                    $state = $newDevice[$j]["state"] == "关灯" ? "00":"01";
                    $ff  .= $state;
                    $status =1;
                    break;
                }
            }
            if($status!==1){
                $ff .= "FF";
            }
            $status =0;
        }
        $parmas = "msg=B1${hotelId}${roomId}FFFF00A701AFFFFFFFFFFEBEEA48${ff}B1B1AAAA0D0A";
        $aParams = ["hotelnum"=>$hotelId,"data"=>$parmas];
        $sHttpRes = $httpClient->post("https://www.miulink.com/smarthotel/Control", ['body' => http_build_query($aParams)])->getBody()->getContents();

        return $sHttpRes;
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

    public function updateMode($roomId,$hotelId,$mode,$status="STATE_ON")
    {
        $deviceId = $mode ==1 ? "所有灯":"关闭所有灯";
        self::setMode($roomId,$hotelId,$deviceId,$status);
    }
}