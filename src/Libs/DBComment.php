<?php
namespace Qscmf\Utils\Libs;

class DBComment{

    static public function buildChangeSql(string $ddl, array $comment_mapping):string{
        $ddl = self::fetchDDL($ddl);
        $ddl_list = self::parseDDLToArray($ddl);

        return self::addCommentByMapping($ddl_list, $comment_mapping);
    }

    static protected function fetchDDL(string $ddl):string{
        if (is_file($ddl)){
            return require_once($ddl);
        }

        return $ddl;
    }

    static protected function parseDDLToArray(string $ddl): array
    {
        $result = [];
        $createTablePattern = '/CREATE TABLE `(\w+)` \((.*?)\) ENGINE=(?:[\w\s\d\=]+)(?:\'(.*?)\')?/s';
        $columnPattern = '/`(?P<name>.+?)` (?P<definition>.+?)(?: COMMENT (?:\'(?P<comment>.+?)\'))?,\r?\n/is';

        if (preg_match_all($createTablePattern, $ddl, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tableInfo = [
                    'name' => $match[1],
                    'comment' => $match[3] ?? '',
                    'column' => [],
                ];
                $columnInfo = [];
                preg_replace_callback($columnPattern, function ($col_match) use(&$columnInfo){
                    $columnInfo[$col_match['name']] = [
                        'definition' => $col_match['definition'],
                        'comment' => $col_match['comment'] ?? '',
                    ];
                }, $match[2]);
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