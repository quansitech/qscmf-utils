<?php
namespace Qscmf\Utils\Libs;

class DingTalkRobot{

    protected static string $uri = 'https://oapi.dingtalk.com/robot/send';

    protected static string $msg_type = 'text';
    protected static string $content_prefix = 'quansitech';

    protected static function getAccessToken():string{
        return env("DING_TALK_ACCESS_TOKEN");
    }

    protected static function buildData(string $msg):array{
        return [
            'msgtype' => self::$msg_type,
            'text' => [
                'content' => self::$content_prefix . ': ' . $msg
            ]
        ];
    }

   protected static function postJson($url, $params, $header = array()): bool|string
   {
       $url = self::$uri.$url;

       $post_val = is_array($params) ? json_encode($params) : $params;
       $header[] = 'Content-Type: application/json';
       $header[] = 'Content-Length: ' . strlen($post_val);

        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );

        $params = $post_val;
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_POST] = 1;
        $opts[CURLOPT_POSTFIELDS] = $params;

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) E('请求发生错误：' . $error);
        return  $data;
    }

    protected static function getOptions($options = []):array{
        $def = ['http_errors' => false];
        return array_merge((array)$options, $def);
    }

    public static function send(string $msg, ?string $access_token = ''){
        $data = self::buildData($msg);

        return self::postJson('?access_token='.($access_token ?: self::getAccessToken()), $data);
    }

}