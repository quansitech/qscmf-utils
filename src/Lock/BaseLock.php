<?php
namespace Qscmf\Utils\Lock;

use Illuminate\Support\Str;
use Think\Cache;

class BaseLock{

    protected static $redis;
    protected $uuid;
    protected $key;

    public function __construct($key){
        if(!self::$redis){
            self::$redis = Cache::getInstance('redis');
        }
        $this->key = self::$redis->getOptions('prefix') . $key;
        $this->uuid = Str::uuid();
    }

}