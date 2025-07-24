<?php
namespace Qscmf\Utils\Libs;

use Think\Cache;

class Common{

    static function imageproxy($options, $file_id, $cache = ''){
        if(is_null($file_id)){
            return null;
        }

        if(filter_var($file_id, FILTER_VALIDATE_URL)){
            $path = $file_id;
            $uri = $file_id;
        }else if(is_array($file_id)){
            $file_ent = $file_id;
        }
        else{
            $file_pic_model = M('FilePic');
            if($cache){
                $file_pic_model->cache($cache);
            }
            $file_ent = $file_pic_model->find($file_id);

        }

        if($file_ent){
            $file_path = UPLOAD_PATH . '/' . $file_ent['file'];
            $path = $file_ent['file'] ? ltrim($file_path, '/') : $file_ent['url'];
            $uri = $file_ent['file'] ? HTTP_PROTOCOL .  '://' . DOMAIN . $file_path : $file_ent['url'];
        }


        $format = env('IMAGEPROXY_URL');
        $remote = env("IMAGEPROXY_REMOTE");
        if($remote){
            $remote_parse = parse_url($remote);
            $schema = $remote_parse['scheme'];
            $domain = $remote_parse['host'];
        }
        else{
            $schema = HTTP_PROTOCOL;
            $domain = SITE_URL;
        }
        $format = str_replace("{schema}", $schema, $format);
        $format = str_replace("{domain}", $domain, $format);
        $format = str_replace("{options}", $options, $format);
        $format = str_replace("{path}", $path, $format);
        $format = str_replace("{remote_uri}", $uri, $format);

        return $format;
    }

    static function cached(callable $function, int $expire = 0, string $key = '', string $group = '')
    {
        $default_key = function($args) use ($function){
            $r = new \ReflectionFunction($function);
            $fuc_footprint = md5('f_' . $r->getFileName() . '_s_' . $r->getStartLine() . '_e_' . $r->getEndLine());

            $key = md5($fuc_footprint . '_k_' . serialize($args));

            return $key;
        };
        return function () use ($function, $default_key, $expire, $key, $group) {
            $args = func_get_args();
            // 锁过期时间, 单位秒
            $lock_expire = env("UTIL_CACHE_LOCK_EXPIRE", 60);
            // 预刷新时间，单位秒
            $refresh_before_expire = env("UTIL_CACHE_REFRESH_BEFORE_EXPIRE", 30);
            if(!$key){
                $key = $default_key($args);
            }
            $lock_key = $key . '_lock';
            $cache_data = S($key);

            $redis = Cache::getInstance('redis');
            $ttl = $redis->ttl($key);
            
            $redis_lock_cls = RedisLock::getInstance();

            $run_function = function($function, $args, $expire, $key, $group, $redis){
                $cache_data = call_user_func_array($function, $args);
                $cache_data = $cache_data === null ? PHP_NULL : $cache_data;
                S($key, $cache_data, $expire);
                if($group != ''){
                    $redis->sAdd($group, $key);
                }

                return $cache_data !== PHP_NULL ? $cache_data : null;
            };

            if ($cache_data === false) {
                
                list($is_lock, $cache_data) = $redis_lock_cls->lockWithCallback($lock_key, $lock_expire, function () use ($key) {
                    $data = S($key);
                    return [$data !== false, $data];
                }, 30, 100000);

                if ($is_lock === false) {
                    throw new CachedFailureException('cached failure');
                } else if ($is_lock === true) {
                    $cache_data = $run_function($function, $args, $expire, $key, $group, $redis);

                    $redis_lock_cls->unlock($lock_key);
                }

            } else if ($expire > 0 && $ttl > 0 && $ttl <= $refresh_before_expire) {
                // 缓存即将过期，尝试预刷新
                $is_refreshing_key = $key . '_refreshing';
                if ($redis_lock_cls->lock($is_refreshing_key, $refresh_before_expire)) {
 
                    $cache_data = $run_function($function, $args, $expire, $key, $group, $redis);

                    //$redis_lock_cls->unlock($is_refreshing_key);
                }
                // 直接返回旧缓存
                return $cache_data;
            }
            
            return $cache_data;
        };
    }

    static public function clearCachedGroup($group){
        $redis = Cache::getInstance('redis');
        $keys = $redis->sMembers($group);
        foreach($keys as $key){
            $redis->del($key);
        }
        $redis->del($group);
    }
}