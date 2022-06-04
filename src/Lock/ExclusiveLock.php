<?php
namespace Qscmf\Utils\Lock;

class ExclusiveLock extends BaseLock {

    protected $shared_lock;
    protected $expire;
    protected $timeout;

    public function __construct(string $key, int $expire = 5, int $timeout = 0) {
        parent::__construct($key);

        $this->expire = $expire;
        $this->timeout = $timeout;
    }


    public function lock() : bool{
        $start_time = time();
        while (true){
            $is_lock = self::$redis->eval($this->lockLua());
            if ($is_lock){
                return true;
            }

            if ($this->timeout <= 0 || $start_time+$this->timeout < microtime(true)) break;
            usleep(100000);
        }
        return false;
    }

    public function unlock() : void {
        $unlock = $this->shared_lock ? $this->shared_lock->unlockLua() : '';
        $lua = <<<LUA
{$unlock}
local uuid = redis.call('get', '{$this->key}')
if uuid == "{$this->uuid}" then
    redis.call('del', '{$this->key}')
end
LUA;

        self::$redis->eval($lua);
    }

    public function register(SharedLock $lock) : self{
        $this->shared_lock = $lock;
        return $this;
    }

    protected function lockLua() : string{
        $check = $this->shared_lock ? $this->shared_lock->checkLua() : '';
        $lock = $this->shared_lock ? $this->shared_lock->lockLua($this->expire) : '';
        $unlock = $this->shared_lock ? $this->shared_lock->unlockLua() : '';
        $lua = <<<LUA
{$check}
{$lock}
local is_lock = redis.call('set', '{$this->key}', '{$this->uuid}', 'EX', {$this->expire}, 'NX')
if is_lock then
    return 1
else
    {$unlock}
    return 0
end
LUA;
        return $lua;
    }

}