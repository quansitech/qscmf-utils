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

### RedisLock

    基于Redis改造的悲观锁
    + 先获取锁再执行业务逻辑，执行结束释放锁。
    + 保证同一个方法的并发重复操作请求只有一个请求可以获取锁，在不进行高延迟事务处理的场景下可以使用。
    
    ##### lock
    ```blade
    该方法可以获取锁
    
    参数 
    $key 名称
    $expire 过期时间 单位为秒
    $timeout  循环取锁时间 单位为秒，默认为0
    $interval 取锁失败后重试间隔时间 单位为微秒，默认为100000
    
    返回值
    锁成功返回true 锁失败返回false
    ```
    
    ##### unlock
    ```blade
    该方法可以释放锁
    
    参数 
    $key 名称
    
    返回值
    释放锁的个数
    ```
    ##### 代码示例
    ```php
    public function execShell(){
        $redis_lock = \Qscmf\Lib\RedisLock::getInstance();
        $is_lock = $redis_lock->lock('exec_shell_lock_key', 60);
        $is_lock === false && $this->error('请一分钟后再操作');
    
        shell_exec('ll >/dev/null');
        
        $redis_lock->unlock('exec_shell_lock_key');
    }
    ```