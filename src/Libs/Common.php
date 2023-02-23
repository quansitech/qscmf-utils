<?php
namespace Qscmf\Utils\Libs;

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

    static function cached(callable $function, int $expire = 0){
        $r = new \ReflectionFunction($function);
        $fuc_footprint = md5('f_' . $r->getFileName() . '_s_' . $r->getStartLine() . '_e_' . $r->getEndLine());
        return function() use($function, $fuc_footprint, $expire) {
            $args = func_get_args();
            $key = md5($fuc_footprint . '_k_' . serialize($args));
            $lock_key = $key . '_lock';
            $cache_data = S($key);
            if ($cache_data === false) {
                $redis_lock_cls = new RedisLock();
                list($is_lock, $cache_data) = $redis_lock_cls->lockWithCallback($lock_key, $expire, function () use ($key) {
                    $data = S($key);
                    return [!!$data, $data];
                }, 30, 100000);

                if ($is_lock === false) {
                    throw new CachedFailureException('cached failure');
                } else if ($is_lock === true) {
                    $cache_data = call_user_func_array($function, $args);
                    $cache_data = $cache_data === null ? PHP_NULL : $cache_data;
                    S($key, $cache_data, $expire);
                    $redis_lock_cls->unlock($lock_key);
                }

            }
            return $cache_data;
        };
}