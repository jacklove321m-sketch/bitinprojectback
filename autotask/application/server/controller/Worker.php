<?php
namespace app\server\controller;
use think\worker\Server;
 
class Worker extends Server
{
 
    public function onWorkerStart($work)
    {
        $handle=new Collection();
        $handle->add_timer();
    }
 
 
}

?>