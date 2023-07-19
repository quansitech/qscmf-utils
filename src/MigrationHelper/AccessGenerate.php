<?php
namespace Qscmf\Utils\MigrationHelper;

use Illuminate\Support\Facades\DB;

class AccessGenerate{

    static public function add(int $role_id, string $module, string $controller, string $action) : void{
        $module_node = DB::table("qs_node")->where('level', 1)->where('name', $module)->first();
        if (!$module_node){
            throw new \Exception("模块不存在");
        }

        $controller_node = DB::table("qs_node")->where('level', 2)->where('name', $controller)->where('pid', $module_node->id)->first();
        if (!$controller_node){
            throw new \Exception("控制器不存在");
        }

        $action_node = DB::table("qs_node")->where('level', 3)->where('name', $action)->where('pid', $controller_node->id)->first();
        if (!$action_node){
            throw new \Exception("权限点不存在");
        }

        $action_access = DB::table("qs_access")->where('role_id', $role_id)->where('node_id', $action_node->id)->first();
        if (!$action_access){
            DB::table("qs_access")->insert([
                'role_id' => $role_id,
                'node_id' => $action_node->id,
                'level' => 3,
                'module' => $action_node->name
            ]);
        }

        $controller_access = DB::table("qs_access")->where('role_id', $role_id)->where('node_id', $controller_node->id)->first();
        if(!$controller_access){
            DB::table("qs_access")->insert([
                'role_id' => $role_id,
                'node_id' => $controller_node->id,
                'level' => 2,
                'module' => $controller_node->name
            ]);
        }

        $module_access = DB::table("qs_access")->where('role_id', $role_id)->where('node_id', $module_node->id)->first();
        if(!$module_access){
            DB::table("qs_access")->insert([
                'role_id' => $role_id,
                'node_id' => $module_node->id,
                'level' => 1,
                'module' => $module_node->name
            ]);
        }
    }

    static public function del(int $role_id, string $module, string $controller, string $action) : void{
        $module_node = DB::table("qs_node")->where('level', 1)->where('name', $module)->first();
        if (!$module_node){
            throw new \Exception("模块不存在");
        }

        $controller_node = DB::table("qs_node")->where('level', 2)->where('name', $controller)->where('pid', $module_node->id)->first();
        if (!$controller_node){
            throw new \Exception("控制器不存在");
        }

        $action_node = DB::table("qs_node")->where('level', 3)->where('name', $action)->where('pid', $controller_node->id)->first();
        if (!$action_node){
            throw new \Exception("权限点不存在");
        }

        DB::table('qs_access')->where('role_id', $role_id)->where('node_id', $action_node->id)->where('level', 3)->delete();
    }
}