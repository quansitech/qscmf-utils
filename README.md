## qscmf辅助开发库

+ 安装
```php
composer require quansitech/qscmf-utils
```

### CmmProcess

    迁移中调用tp的脚本
    
    > 用法：
    >
    > ```php
    > $process = new \Larafortp\CmmMigrate\CmmProcess();
    > //timeout为程序的超时退出时间，默认60秒
    > $process->setTimeOut(100)->callTp('/var/www/move/www/index.php', '/home/index/test');
    > ```

### ConfigGenerator
  
    迁移中处理系统配置的工具类
    
    + addGroup($name) //添加配置分组 
      
    + deleteGroup($name) //删除配置分组
    
    + updateGroup($config_name, $group_name)  //将配置转移到指定分组
    
      以下为新增配置项的操作函数
      > $name 配置名
      >
      > $title 配置标题
      >
      > $value 配置值
      >
      > $remark 配置说明
      >
      > $group 配置分组
      >
      > $sort 排序
      
    + addNum($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增数字类型配置值 
      
    + addText($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增字符类型配置值
    
    + addArray($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增数组类型配置值
    
    + addPicture($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增图片类型配置值
    
    + addUeditor($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增富文本类型配置值
    
    + addSelect($name, $title, $value, $options, $remark = '', $group = 1, $sort = 0) //新增下拉选择配置值 $options 是下拉配置数组
    
    + add($name, $type, $title, $group, $extra, $remark, $value, $sort) //新增配置方法，未预设的第三方组件可使用该函数
    
    + delete($name) //删除配置

### MenuGenerate

### RefModel

    从关联表预提取关联数组（解决N+1循环取数导致数据库频繁访问的问题）
    
    用法
    ```php
    $reader_ents = D("Reader")->where(['status' => 1])->select();
    $school_ref = new RefModel(D('School'), 'id'); //设置目标表的model类  设置目标表的关联id
    $school_ref->fill($reader_ents, 'school_id'); // 通过$reader_ents的school_id预提取关联表的关联数据

    foreach($reader_ents as &$v){
        $v['school_name'] = $school_ref->pick($v['school_id'], 'school_name'); //从预提取到的关联数据拿目标值
    }
    
    ```