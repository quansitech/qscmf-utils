<?php
namespace Qscmf\Utils\Libs;

class AnalysisCUD {

    private array $db_data;
    private array $new_data;

    public function __construct(array $db_data, array $new_data){
        $this->db_data = $db_data;
        $this->new_data = $new_data;
    }

    public function analysis(): array {
        $to_insert = [];
        $to_update = [];
        $to_delete = [];

        // 创建一个map方便查找db_data中的数据
        $db_map = [];
        foreach ($this->db_data as $db_item) {
            if (isset($db_item['id'])) {
                $db_map[$db_item['id']] = $db_item;
            }
        }

        // 分析new_data
        foreach ($this->new_data as $new_item) {
            if (!isset($new_item['id'])) {
                // 没有id的情况下，视为新增数据
                $to_insert[] = $new_item;
            } elseif (isset($db_map[$new_item['id']])) {
                // 有id且能在db_map中找到的情况下，视为更新数据
                $to_update[] = $new_item;
                unset($db_map[$new_item['id']]); // 从db_map中移除，以便最后确定需要删除的数据
            }
        }

        // 分析要删除的数据
        // 剩下在db_map中的是需要从数据库中删除的
        foreach ($db_map as $db_item) {
            $to_delete[] = $db_item;
        }

        return [
            'to_insert' => $to_insert,
            'to_update' => $to_update,
            'to_delete' => $to_delete
        ];
    }
}