<?php
namespace Qscmf\Utils\Libs;

use Illuminate\Support\Facades\DB;

class DBComment{

    static public function buildChangeSql(array $comment_mapping):string{
        $table = array_keys($comment_mapping);
        $ddl = self::fetchDDL($table);
        $ddl_list = self::parseDDLToArray($ddl);

        return self::addCommentByMapping($ddl_list, $comment_mapping);
    }

    static protected function fetchDDL(array $table):string{
        $ddl = '';
        collect($table)->each(function($name) use(&$ddl){
            $sql_str = <<<SQL
SHOW CREATE TABLE {$name};
SQL;

            $sql_obj = DB::select($sql_str);
            $sql = $sql_obj[0]->{'Create Table'}.';'.PHP_EOL;
            $ddl .= $sql;
        });

        return $ddl;
    }

    static protected function parseDDLToArray(string $ddl): array
    {
        $column_str_end_sign = ',__QSCMFACE__';
        $result = [];
        $createTablePattern = '/CREATE TABLE `(\w+)` \((.*?)\) ENGINE=(?:[\w\s\d\=]+)(?:\'(.*?)\')?/s';
        $columnPattern = '/^`(?P<name>.+?)` (?P<definition>.+?)(?: COMMENT (?:\'(?P<comment>.+?)\'))?'.$column_str_end_sign.'/is';

        if (preg_match_all($createTablePattern, $ddl, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tableInfo = [
                    'name' => $match[1],
                    'comment' => $match[3] ?? '',
                    'column' => [],
                ];
                $columnInfo = [];
                $column_definition_list = explode(','.PHP_EOL, $match[2]);

                collect($column_definition_list)->each(function ($one_column_str) use($columnPattern,$column_str_end_sign, &$columnInfo){
                    if (preg_match($columnPattern, trim($one_column_str).$column_str_end_sign, $col_match)){
                        $columnInfo[$col_match['name']] = [
                            'definition' => $col_match['definition'],
                            'comment' => $col_match['comment'] ?? '',
                        ];
                    }
                });
                $tableInfo['column'] = $columnInfo;
                $result[$tableInfo['name']] = $tableInfo;
            }
        }
        return $result;
    }

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

    static protected function extraColumnDefinition(array $column_ddl_list, array $column):array{
        $column_update_list = [];
        collect($column)->each(function($comment, $key) use($column_ddl_list, &$column_update_list){
            $column_update_list[] = "`{$key}` `{$key}` {$column_ddl_list[$key]['definition']} COMMENT '{$comment}'";
        });

        return $column_update_list;
    }

    static protected function combineChangeColumn(array $column_ddl_list, array $column):string{
        $column_update_list = self::extraColumnDefinition($column_ddl_list, $column);

        return ' CHANGE COLUMN '. implode(', CHANGE COLUMN ', $column_update_list);
    }
}