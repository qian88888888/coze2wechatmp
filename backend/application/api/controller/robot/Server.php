<?php

namespace app\api\controller\robot;

use Workerman\Worker;

class Server
{
    public function chat()
    {
        Vendor('workerman.Autoloader');

        $worker = new Worker('websocket://0.0.0.0:5555');
        $worker->count = 8;
        $chat = new Chat();
        $chat->getChatStream($worker);
        Worker::runAll();
    }

}
