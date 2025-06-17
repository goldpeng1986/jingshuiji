<?php
namespace workman;
use think\worker\Server;

class Notice extends Server
{
    protected  $socket = 'websocket://127.0.0.1:7272';
    protected  $processes = 1;

    public function onWorkerStart(){

    }

}