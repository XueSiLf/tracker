<?php


namespace EasySwoole\Tracker;


use EasySwoole\Component\Singleton;
use EasySwoole\Tracker\Excetion\Exception;
use Swoole\Coroutine;

class PointContext
{
    use Singleton;

    protected $deferList = [];
    protected $pointStack = [];
    protected $currentPointStack = [];

    public function start(string $name, ?int $cid = null):Point
    {
        if($cid === null){
            $cid = $this->cid();
        }
        if(!isset($this->pointStack[$cid])){
            $this->pointStack[$cid] = new Point($name);
            $this->currentPointStack[$cid] = $this->pointStack[$cid];
        }
        return $this->pointStack[$cid];
    }

    public function current(?int $cid = null):?Point
    {
        if($cid === null){
            $cid = $this->cid();
        }
        if(isset($this->currentPointStack[$cid])){
            return $this->currentPointStack[$cid];
        }else{
           return null;
        }
    }

    public function next(string $name, ?int $cid = null):Point
    {
        $current = $this->current($cid);
        /*
         * 自动创建
         */
        if($current){
            $point = $this->currentPointStack[$cid];
            $point->next($name);
            $this->currentPointStack[$cid] = $point;
            return $point;
        }else{
            throw new Exception("current point is null,please create start point");
        }
    }

    public function find(string $name,?int $cid = null)
    {

    }

    public function findChild(string $name)
    {

    }

    public function appendChild(string $name, ?int $cid = null)
    {
        if($cid === null){
            $cid = $this->cid();
        }
        if(!isset($this->currentPointStack[$cid])){
            /*
             * 需要先创建开头节点
             */
            throw new Exception("current point is empty");
        }else{
            /** @var Point $point */
            $point = $this->currentPointStack[$cid];
            return $point->appendChild($name);
        }
    }

    private function cid():int
    {
        $cid = Coroutine::getUid();
        if(!isset($this->deferList[$cid]) && $cid > 0){
            $this->deferList[$cid] = true;
            defer(function ()use($cid){
                unset($this->deferList[$cid]);
                if(isset($this->currentPointStack[$cid])){
                    unset($this->currentPointStack[$cid]);
                }
                if(isset($this->pointStack[$cid])){
                    unset($this->pointStack[$cid]);
                }
            });
        }
        return $cid;
    }
}