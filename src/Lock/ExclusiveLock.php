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
            // keys1 share_share_type_lock_key
            // keys2 share_exclusive_type_lock_key
            // keys3 exclusive_lock_key
            // argv1 expire
            // argv2 share_lock_uuid
            // argv3 exclusive_lock_uuid
            $is_lock = self::$redis->eval($this->lockLua(), [
                $this->shared_lock->getKey(SharedLock::TYPE_SHARED),
                $this->shared_lock->getKey(SharedLock::TYPE_EXCLUSIVE),
                $this->key,
                $this->expire,
                $this->shared_lock->getUuid(),
                $this->uuid
            ], 3);
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
local uuid = redis.call('get', KEYS[3])
if uuid == ARGV[3] then
    redis.call('del', KEYS[3])
end
LUA;

        // keys1 share_share_type_lock_key
        // keys2 share_exclusive_type_lock_key
        // keys3 exclusive_lock_key
        // argv1 expire
        // argv2 share_lock_uuid
        // argv3 exclusive_lock_uuid
        self::$redis->eval($lua, [
            $this->shared_lock->getKey(SharedLock::TYPE_SHARED),
            $this->shared_lock->getKey(SharedLock::TYPE_EXCLUSIVE),
            $this->key,
            $this->expire,
            $this->shared_lock->getUuid(),
            $this->uuid
        ], 3);
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
local is_lock = redis.call('set', KEYS[3], ARGV[3], 'EX', ARGV[1], 'NX')
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