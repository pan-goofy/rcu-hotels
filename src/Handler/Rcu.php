<?php
/**
 * Created by PhpStorm.
 * User: guanfangge
 * Date: 4/19/23
 * Time: 5:39 PM
 */

namespace Guanfangge\RcuHotels\Handler;

use Guanfangge\RcuHotels\Control\RcuGeekLink;
use Guanfangge\RcuHotels\Control\RcuMiuLink;
use Guanfangge\RcuHotels\Control\RcuThinke;

class  Rcu
{
    public function __construct($type, $config = "")
    {
        $classs = collect([
            "" => new RcuThinke($config),
            "thinker" => new RcuThinke($config),
            "miulink" => new RcuMiuLink(),
            "geeklink" => new RcuGeekLink()
        ]);
        $this->class = $classs->get($type);
    }

    public function getDevice(array $data)
    {

        return $this->class->getDevice(...$data);
    }

    public function setDevice(array $data)
    {
        return $this->class->setDevice(...$data);
    }
}