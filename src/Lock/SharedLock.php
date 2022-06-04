<?php
namespace Qscmf\Utils\Lock;

class SharedLock extends BaseLock{

    const TYPE_EXCLUSIVE = 1;
    const TYPE_SHARED = 2;
    protected $type;
    protected $shared_key_suffix = 'shared_lock_';
    protected $exclusive_key_suffix = 'exclusive_lock_';

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

    public function lockLua(int $expired = 5): string{
        return match($this->type){
            self::TYPE_SHARED => $this->lockSharedLua($expired),
            self::TYPE_EXCLUSIVE => $this->lockExclusiveLua($expired)
        };
    }

    public function unlockLua(): string{
        return match($this->type){
            self::TYPE_SHARED => $this->unlockSharedLua(),
            self::TYPE_EXCLUSIVE => $this->unlockExclusiveLua()
        };
    }

    protected function unlockSharedLua(): string{
        $key = $this->key . $this->shared_key_suffix;
        return <<<LUA
redis.call('srem', '{$key}', '{$this->uuid}')
LUA;
    }

    protected function unlockExclusiveLua() : string
    {
        $key = $this->key . $this->exclusive_key_suffix;
        return <<<LUA
local exclusive_lock_uuid = redis.call('get', '{$key}')
if exclusive_lock_uuid == "{$this->uuid}" then
    redis.call('del', '{$key}')
end
LUA;

    }

    protected function lockSharedLua(int $expired): string
    {
        $key = $this->key . $this->shared_key_suffix;
        return <<<LUA
redis.call('sadd', '{$key}', '{$this->uuid}')
redis.call('expire', '{$key}', {$expired})
LUA;
    }

    protected function lockExclusiveLua(int $expired): string
    {
        $key = $this->key . $this->exclusive_key_suffix;
        return <<<LUA
redis.call('set', '{$key}', '{$this->uuid}', 'EX', {$expired})
LUA;
    }



    protected function checkSharedLua() : string
    {
        $key = $this->key . $this->exclusive_key_suffix;
        return <<<LUA
local exclusive_lock = redis.call('get', '{$key}')
if exclusive_lock then
    return 0
end
LUA;
    }

    protected function checkExclusiveLua() : string
    {
        $key = $this->key . $this->shared_key_suffix;
        return <<<LUA
local shared_lock_count = redis.call('scard', '{$key}')
if shared_lock_count > 0 then
    return 0
end
LUA;
    }

}