<?php
/**
 * Created by PhpStorm.
 * User: guanfangge
 * Date: 4/19/23
 * Time: 5:39 PM
 */
namespace  Guanfangge\RcuHotels\Handler;
class  Rcu{

    public function getDevice(\Guanfangge\RcuHotels\Contracts\Rcu $class,array $data){
        $this->class = $class;
        return $this->class->getDevice(...$data);
    }
    public function setDevice(\Guanfangge\RcuHotels\Contracts\Rcu $class,array $data){
        $this->class = $class;
        return $this->class->setDevice(...$data);
    }
}