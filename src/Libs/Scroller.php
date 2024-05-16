<?php
namespace Qscmf\Utils\Libs;

class Scroller{

    protected array $order_arr = [];
    protected array $sort_map = [
        'asc' => 'gt',
        'desc' => 'lt'
    ];

    public function __construct(string $order)
    {
        $this->order_arr = collect(explode(',', $order))->map(function($item){
            $arr = explode(' ', trim($item));
            $alias_arr = explode('.', $arr[0]);
            if(count($alias_arr) > 1){
                $column = $alias_arr[1];
                $alias = $alias_arr[0];
            }
            else{
                $column = $alias_arr[0];
                $alias = '';
            }
            return [
                'column' => $column,
                'sort' => $arr[1] ?? 'asc',
                'alias' => $alias
            ];
        })->toArray();
    }

    public function toLastCondition(array $last_item) : string{
        $last_condition = collect($this->order_arr)->map(function($item)use($last_item){
            return [
                $item['column'] => $last_item[$item['column']]
            ];
        })->collapse()->toArray();
        return $this->lastConditionFromArr($last_condition);
    }

    public function applyLastCondition(array &$map, string $last_condition){
        $last_condition = $this->lastConditionFromStr($last_condition);

        $condition_length = count($last_condition);
        if($condition_length == 1){
            $first_column = $this->order_arr[0]['column'];
            $first_sort = $this->order_arr[0]['sort'];
            $alias = $this->order_arr[0]['alias'];
            $full_column = $alias ? $alias . '.' . $first_column : $first_column;
            $condition = [
                $full_column => [$this->sort_map[$first_sort], $last_condition[$first_column]]
            ];
            $map['_complex'] = $condition;
        }
        else if($condition_length == 2){
            $first_column = $this->order_arr[0]['column'];
            $second_column = $this->order_arr[1]['column'];
            $first_sort = $this->order_arr[0]['sort'];
            $second_sort = $this->order_arr[1]['sort'];
            $first_alias = $this->order_arr[0]['alias'];
            $second_alias = $this->order_arr[1]['alias'];
            $full_first_column = $first_alias ? $first_alias . '.' . $first_column : $first_column;
            $full_second_column = $second_alias ? $second_alias . '.' . $second_column : $second_column;
            $inner_condition = [
                $full_first_column => $last_condition[$first_column],
                $full_second_column => [$this->sort_map[$second_sort], $last_condition[$second_column]]
            ];

            $condition = [
                $full_first_column => [$this->sort_map[$first_sort], $last_condition[$first_column]],
                '_complex' => $inner_condition,
                '_logic' => 'or'
            ];
            $map['_complex'] = $condition;
        }
        else{
            throw new \Exception("暂不支持多于两个字段的排序");
        }
    }

    protected function lastConditionFromArr(array $last_condition) : string{
        $res = [];
        foreach($last_condition as $k => $v){
            $res[] = urlencode($k) . '=' . urlencode($v);
        }

        return base64_url_encode(implode('&', $res));
    }

    protected function lastConditionFromStr(string $last_condition) : array{
        $str = base64_url_decode($last_condition);
        $arr = explode("&", $str);
        $res = [];
        foreach($arr as $v){
            list($k, $v) = explode("=", $v);
            $res[urldecode($k)] = urldecode($v);
        }
        return $res;
    }
}