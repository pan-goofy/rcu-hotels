<?php

declare(strict_types=1);

namespace Guanfangge\RcuHotels\Contracts;

interface Rcu
{
    /**
     * 获取设备
     */
    public function getDevice($roomId,$hotelCode,$deviceType);
    /**
     * 修改设备
     */
    public function setDevice($roomId,$hotelCode,$deviceId,$deviceState,$action,$deviceAttr,$attrValue);

    /**
     * 修改模式
     */
    public function updateMode($roomId,$hotelId,$mode,$status);

}