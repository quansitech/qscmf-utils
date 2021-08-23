<?php


namespace Qscmf\Utils\MigrationHelper;


use Illuminate\Support\Facades\DB;

/**
 * Class AuthNodeGenerate
 * @package Qscmf\Utils\MigrationHelper
 *
 * 生成权限点
 *
 */

class AuthNodeGenerate
{

    const LEVEL_MODULE = 1;
    const LEVEL_CONTROLLER = 2;
    const LEVEL_ACTION = 3;

    /**
     * @param string|array $module_name 模块名
     * @param string|array $controller_name 控制器名
     * @param string $action_name 权限点名
     * @param string $title 权限点标题
     * @param int $pid 父节点，若为空则根据模块、控制器查找
     * @return bool
     * @throws \Exception
     */

    static public function addAuthNode($module_name, $controller_name, $action_name, $title, $pid = 0){
        DB::beginTransaction();
        try {
            if (!$pid){
                list($m_name, $m_title) = is_array($module_name) ? $module_name : [$module_name,$module_name]; ;
                list($c_name, $_title) =  is_array($controller_name) ? $controller_name : [$controller_name,$controller_name]; ;

                $module_id = self::notExistThenInsertModule($m_name, $m_title);
                $pid = self::notExistThenInsertController($c_name, $_title, $module_id);
            }
            self::notExistThenInsertAction($action_name, $title, $pid);
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return true;

    }

    static protected function notExistThenInsertAction($name, $title, $pid){
        return self::notExistThenInsert($name, self::LEVEL_ACTION, $title, $pid);
    }

    static protected function notExistThenInsertModule($name, $title){
        return self::notExistThenInsert($name, self::LEVEL_MODULE, $title);
    }

    static protected function notExistThenInsertController($name, $title, $pid){
        return self::notExistThenInsert($name, self::LEVEL_CONTROLLER, $title, $pid);
    }

    static protected function notExistThenInsert($name, $level, $title = '', $pid = 0){
        $id =  self::fetchId($name, $level, $pid);
        if (!$id){
            $id = self::insertNodeGetId($name,$level, $title, $pid);
        }

        return $id;
    }

    static public function fetchId($name, $level, $pid = 0){
        return  DB::table('qs_node')->where('name',$name)->where('level',$level)->where('pid', $pid)->value('id');
    }

    static protected function insertNodeGetId($name, $level, $title = '', $pid = 0){
        return DB::table('qs_node')->insertGetId([
            'name'=>$name,
            'title'=>$title?:$name,
            'status'=>1,
            'pid'=>$pid,
            'level'=>$level
        ]);
    }

    /**
     * @param string $module_name 模块名
     * @param string $controller_name 控制器名
     * @param string $action_name 权限点名，若为空则删除该控制器下的所有权限点
     */
    static public function deleteAuthNode($module_name, $controller_name, $action_name = ''){
        $module_id = self::fetchId($module_name, self::LEVEL_MODULE);
        $controller_id = self::fetchId($controller_name, self::LEVEL_CONTROLLER, $module_id);
        if ($action_name){
            DB::table('qs_node')
                ->where('name', $action_name)
                ->where('level', self::LEVEL_ACTION)
                ->where('pid', $controller_id)
                ->delete();
        }else{
            DB::table('qs_node')
                ->where('pid', $controller_id)
                ->orWhere('id', $controller_id)
                ->delete();
        }

    }

}