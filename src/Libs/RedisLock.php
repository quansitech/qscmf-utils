<?php
/**
 * Created by PhpStorm.
 * User: xh
 * Date: 2019/6/6
 * Time: 10:23
 */

namespace Qscmf\Utils\Libs;

use Illuminate\Support\Str;
use Think\Cache;

class RedisLock
{
    protected $redis;

    protected $uuid;

    private static $_instance = [];

    public function __construct($config = [])
    {
        $this->redis = Cache::getInstance('redis', $config);
        $this->uuid = Str::uuid();
    }


    /**
     *
     * 取得类实例化对象
     *
     * @param $config   array
     * @return self   object
     */
    static function getInstance($config = []){
        $guid = to_guid_string($config);
        if (!(self::$_instance[$guid] instanceof self)){
            self::$_instance[$guid] = new self($config);
        }
        return self::$_instance[$guid];
    }


    /**
     *
     * 锁的状态
     *
     * @param $key      string  名称
     * @param $expire   int     过期时间 单位为秒
     * @param $timeout  int     循环取锁时间 单位为秒
     * @param $interval int     取锁失败后重试间隔时间 单位为微秒
     * @return bool             锁成功返回true 锁失败返回false
     */
    public function lock($key, int $expire, int $timeout = 0, int $interval = 100000){
        $start_time = time();

        while (true){
            $is_lock = $this->redis->set($key, $this->uuid, $expire, 'nx');
            if ($is_lock){
                return true;
            }

            if ($timeout <= 0 || $start_time+$timeout < microtime(true)) break;
            usleep($interval);
        }
        return false;
    }

    //等待锁，但不会上锁
    public function waitLock(string $key, int $timeout = 10, int $interval = 100000) : bool{
        $start_time = time();
        while(true){
            if(!$this->redis->get($key)){
                return true;
            }
            if ($timeout <= 0 || $start_time+$timeout < microtime(true)) break;
            usleep($interval);
        }

        return false;
    }

    /**
     *
     * 判断锁是否过期
     *
     * @param $expire   int 过期时间
     * @return bool         已过期返回true 未过期返回false
     */
    public function isTimeExpired($expire){
        return $expire < time();
    }

    /**
     *
     * 释放锁
     *
     * @param $key  string|array    名称
     * @return int | boolean               释放锁的个数
     *  false表示释放失败
     */
    public function unlock($key){
        $value = $this->redis->get($key);
        if($value === $this->uuid){
            return $this->redis->del($key);
        }
        return false;
    }

    /**
     *
     * 获取Redis原方法
     *
     * @return \Redis
     */
    public function getRedis(){
        return $this->redis;
    }

}