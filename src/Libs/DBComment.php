<?php
namespace Qscmf\Utils\Libs;

use Illuminate\Support\Facades\DB;

class DBComment{
    static protected function addCommentByMapping(array $ddl_list, array $mapping):string{
        $sql = '';
        foreach ($mapping as $table => $info){
            $table_comment = is_array($info) ? $info['comment'] : $info;
            $tmp_sql = <<<SQL
ALTER TABLE `{$table}`  COMMENT = '{$table_comment}'
SQL;

            if (is_array($info) && $info['column']){
                $column = $info['column'];
                $column_str = self::combineChangeColumn($ddl_list[$table]['column'], $column);

                $column_str && $tmp_sql .= ', '.$column_str;
            }

            $sql.=$tmp_sql.' ;';
        }

        return $sql;
    }

    static protected function extraByDDLList(array $column_ddl_list, array $column):array{
        $column_update_list = [];
        collect($column)->each(function($comment, $key) use($column_ddl_list, &$column_update_list){
            $column_update_list[] = "`{$key}` `{$key}` {$column_ddl_list[$key]['definition']} COMMENT '{$comment}'";
        });

        return $column_update_list;
    }

    static protected function combineChangeColumn(array $column_ddl_list, array $column):string{
        $column_update_list = self::extraByDDLList($column_ddl_list, $column);

        return ' CHANGE COLUMN '. implode(', CHANGE COLUMN ', $column_update_list);
    }

    // ['table'=>['name'=>'table','column'=>['id'=>['definition'=>'', 'comment'=>'', 'name' => '']]]]
    static protected function fetchColumnListGroupByTable(array $table):array{
        $schema =  DB::connection()->getDatabaseName();
        $table_str = "('".collect($table)->implode("','")."')";
        $column_list_sql = <<<SQL
select TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,
       IF(ISNULL(CHARACTER_SET_NAME),'',concat(' CHARACTER SET ',CHARACTER_SET_NAME)) CHARACTER_SET_NAME,
       IF(ISNULL(COLLATION_NAME),'',concat(' COLLATE ',COLLATION_NAME)) COLLATION_NAME ,
       IF(IS_NULLABLE = 'NO', ' NOT NULL ', '') IS_NULLABLE ,
       IF(ISNULL(COLUMN_DEFAULT), '', CONCAT(' DEFAULT ', IF(COLUMN_DEFAULT !='',COLUMN_DEFAULT,"''"))) COLUMN_DEFAULT ,
       COLUMN_COMMENT,
       EXTRA 
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = '{$schema}'
AND TABLE_NAME in {$table_str};
SQL;
        $column_list_obj = \Illuminate\Support\Facades\DB::select($column_list_sql);
        if (empty($column_list_obj)){
            return [];
        }

        $table_list = [];
        collect($column_list_obj)->each(function($item) use(&$table_list){
            $item_temp = clone $item;
            unset($item_temp->TABLE_NAME);
            unset($item_temp->COLUMN_NAME);
            unset($item_temp->COLUMN_COMMENT);

            $definition = '';
            foreach ($item_temp as  $key=>$v){
                $definition .= $v;
            }
            $table_list[$item->TABLE_NAME][$item->COLUMN_NAME] = [
                'name' => $item->COLUMN_NAME,
                'definition' => $definition,
                'comment' => $item->COLUMN_COMMENT,
            ];
        });

        return $table_list;
    }

    static protected function combineDDLList(array $table):array{
        $info_table_list = self::fetchColumnListGroupByTable($table);

        $ddl_list = [];
        collect($table)->each(function($item) use($info_table_list, &$ddl_list){
            $ddl_list[$item] = [
                'name' => $item,
                'comment' => '',
                'column' => $info_table_list[$item]
            ];
        });

        return $ddl_list;
    }

    static public function buildChangeSql(array $comment_mapping):string{
        $table = array_keys($comment_mapping);
        $ddl_list = self::combineDDLList($table);

        return self::addCommentByMapping($ddl_list, $comment_mapping);
    }

}