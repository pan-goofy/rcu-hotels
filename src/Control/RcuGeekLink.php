<?php

namespace Guanfangge\RcuHotels\Control;


use Guanfangge\RcuHotels\Contracts\Rcu;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class RcuGeekLink implements Rcu
{
    public $username;
    public $password;
    public $appid;
    public $appkey;

    public function __construct($config){
        $this->username = $config['username'] ?? "";
        $this->password = $config['password'] ?? "";
        $this->appid = $config['appid'] ?? "";
        $this->appkey = $config['appkey'] ?? "";
    }

    public function getDevice($roomId="", $hotelId="", $deviceType="")
    {
        $random = rand(0,65024);
        $time = time();
        $url = "https://hotel.geeklink.com.cn/open/io.php?random={$random}&time={$time}";
        $parmas = [
            "method"=> "getDeviceRequest",
            "home_id"=> "E5416E894FEB1268F3988707B579FA27",
            "seq"=> $time+$random
        ];
        $appkey = $this->appkey;
        $sign =  hash("sha256","appkey={$appkey}&random={$random}&time={$time}");
        //模式
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ["appid"=>$this->appid,"session"=>$this->getSession(),"sign" =>$sign]
            ]
        );
        $sHttpRes = $httpClient->get($url, ['json' => $parmas])->getBody()->getContents();
        $collect =  json_decode($sHttpRes)->devices;
        $response  = $collect->map(function ($item){
            $item['id'] = $item['md5'];
            return $item;
        });
        $data['Data'] = $response;
        return $data;
    }

    public function setDevice($roomId,$hotelId,$deviceId="",$deviceState="",$action="",$deviceAttr,$attrValue)
    {
        $random = rand(0,65024);
        $time = time();
        $url = "https://hotel.geeklink.com.cn/open/io.php?random={$random}&time={$time}";
        $parmas = [
            "method"=> "ctrlDeviceRequest",
            "home_id"=> "E5416E894FEB1268F3988707B579FA27",
            "seq"=> $time+$random,
            "sub_id"=> "1",
            "md5"=>  $deviceId,
            "type"=> "fb",
            "data"=> [
                "opt"=>"one",
                "which"=> 1,
                "switch"=> 1,
            ],
        ];
        $appkey = $this->appkey;
        $sign =  hash("sha256","appkey={$appkey}&random={$random}&time={$time}");
        //模式
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => ["appid"=>$this->appid,"session"=>$this->getSession(),"sign" =>$sign]
            ]
        );
        $sHttpRes = $httpClient->get($url, ['json' => $parmas])->getBody()->getContents();
        $data['Data'] = json_decode($sHttpRes)->devices;
        return $data;
    }

    public function getSession()
    {
        $cachePool = new FilesystemAdapter('', 3600, "cache");
        $demoString = $cachePool->getItem('session');
        if(!$cachePool->hasItem('session')){
             self::setSession($demoString,$cachePool);
        }
        return $demoString->get();
    }

    public  function setSession($demoString,$cachePool)
    {
        $random = rand(0,65024);
        $time = time();
        $url = "https://hotel.geeklink.com.cn/open/io.php?random={$random}&time={$time}";
        $parmas = [
            "method"=> "getSessionRequest",
            "username"=> $this->username,
            "password"=> $this->password,
            "seq"=> $time+$random
        ];
        $appkey = $this->appkey;
        $sign =  hash("sha256","appkey={$appkey}&random={$random}&time={$time}");
        //模式
        $httpClient = new Client([
                'timeout' => 60,
                'headers' => [   "appid"=>$this->appid, "sign" =>$sign]
            ]
        );
        $sHttpRes = $httpClient->get($url, ['json' => $parmas])->getBody()->getContents();
        $session = json_decode($sHttpRes)->session;
        $demoString->set($session);
        $cachePool->save($demoString);
    }

}