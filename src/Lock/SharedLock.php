<?php
namespace Qscmf\Utils\Lock;

class SharedLock extends BaseLock{

    const TYPE_EXCLUSIVE = 1;
    const TYPE_SHARED = 2;
    protected $type;
    protected $shared_key_suffix = '_shared_lock';
    protected $exclusive_key_suffix = '_exclusive_lock';

    public function __construct(string $key, int $type = self::TYPE_SHARED){
        parent::__construct($key);

        $this->type = $type;
    }

    public function checkLua() : string{
        return match($this->type) {
            self::TYPE_SHARED => $this->checkSharedLua(),
            self::TYPE_EXCLUSIVE => $this->checkExclusiveLua()
        };
    }

    public function lockLua(): string{
        return match($this->type){
            self::TYPE_SHARED => $this->lockSharedLua(),
            self::TYPE_EXCLUSIVE => $this->lockExclusiveLua()
        };
    }

    public function unlockLua(): string{
        return match($this->type){
            self::TYPE_SHARED => $this->unlockSharedLua(),
            self::TYPE_EXCLUSIVE => $this->unlockExclusiveLua()
        };
    }

    protected function unlockSharedLua(): string{
        return <<<LUA
redis.call('srem',KEYS[1], ARGV[2])
LUA;
    }

    protected function unlockExclusiveLua() : string
    {
        return <<<LUA
local exclusive_lock_uuid = redis.call('get', KEYS[2])
if exclusive_lock_uuid == ARGV[2] then
    redis.call('del', KEYS[2])
end
LUA;
    }

    public function getKey(int $type) : string{
        return match($type) {
            self::TYPE_SHARED => $this->key . $this->shared_key_suffix,
            self::TYPE_EXCLUSIVE => $this->key . $this->exclusive_key_suffix
        };
    }

    protected function lockSharedLua(): string
    {
        return <<<LUA
redis.call('sadd', KEYS[1], ARGV[2])
redis.call('expire', KEYS[1], ARGV[1])
LUA;
    }

    protected function lockExclusiveLua(): string
    {
        return <<<LUA
redis.call('set', KEYS[2], ARGV[2], 'EX', ARGV[1])
LUA;
    }



    protected function checkSharedLua() : string
    {
        return <<<LUA
local exclusive_lock = redis.call('get', KEYS[2])
if exclusive_lock then
    return 0
end
LUA;
    }

    protected function checkExclusiveLua() : string
    {
        return <<<LUA
local shared_lock_count = redis.call('scard', KEYS[1])
if shared_lock_count > 0 then
    return 0
end
LUA;
    }

}