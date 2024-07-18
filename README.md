# qscmf辅助开发库

+ 安装
  
  ```php
  composer require quansitech/qscmf-utils
  ```

## 

## CmmProcess

迁移中调用tp的脚本

> 用法：
> 
> ```php
> $process = new \Qscmf\Utils\MigrationHelper\CmmProcess();
> //timeout为程序的超时退出时间，默认60秒
> $process->setTimeOut(100)->callTp('/var/www/move/www/index.php', '/home/index/test');
> ```

## 

## ConfigGenerator

迁移中处理系统配置的工具类

+ addGroup($name) //添加配置分组 

+ deleteGroup($name) //删除配置分组

+ updateGroup($config_name, $group_name)  //将配置转移到指定分组

+ getGroupId($group_name) //根据分组名获取分组id
  
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

+ updateSort($name, $sort) //修改配置的排序

+ delete($name) //删除配置

## 

## MenuGenerate

生成菜单和节点列表
自动处理menu和node的关系

#### 用法

+ 生成top_menu为平台的菜单和节点列表
  
  ```php
  $this->nodeData = [
   '新闻中心'=> [
             [
                 'name'      => 'index',
                 'title'     => '新闻分类',
                 'controller'=> 'NewsCate',
             ],
             [
                 'name'      => 'index',
                 'title'     => '内容管理',
                 'controller'=> 'News',
                 'sort'      => 1,
             ],
   ],
  ];
  
  $menuGenerate = new Qscmf\Utils\MigrationHelper\MenuGenerate();
  $menuGenerate->insertAll($this->nodeData);

  // 撤销
  $menuGenerate->insertAllRollback($this->nodeData);
  ```


```
+ 生成自定义top_menu的菜单和节点列表
```php
$data = [
            [
                'title'      => '平台2', //标题              (必填)
                'module'     => 'newsAdmin', //模块英文名        (必填)
                'module_name'=> '后台管理', //模块中文名   (必填)
                'url'        => '', //url                  (必填)
                'type'       => '', //类型                (选填）
                'sort'       => 0, //排序                (选填）
                'icon'       => '', //icon                (选填）
                'status'     => 1, //状态              (选填）
                'top_menu'   => [
                    '新闻中心'=> [
                        [
                            'name'      => 'index',       //（必填）
                            'title'     => '测试新闻中心',    //（必填）'
                            'controller'=> 'NewsController', //（必填）
                            'sort'      => 1, //排序       //（选填）
                            'icon'      => '', //图标        //（选填）
                            'remark'    => '', //备注      //（选填）
                            'status'    => 1, //状态        //（选填）
                        ],
                    ],
                ],
            ],
        ];

$menuGenerate = new Qscmf\Utils\MigrationHelper\MenuGenerate();
$menuGenerate->insertNavigationAll($data);

// 撤销
$menuGenerate->insertNavigationAllRollback($data);
```

+ 通过controller_title字段可自定义控制器title，默认为controller
  
  ```text
  controller为英文，对用户来说不太好理解，使用自定义中文说明更友好。
  ```

```php
$this->nodeData = [
    '新闻中心'=> [
              [
                  'name'      => 'index',
                  'title'     => '新闻分类',
                  'controller'=> 'NewsCate',
                  'controller_title'=> '新闻分类管理',
              ],
              [
                  'name'      => 'index',
                  'title'     => '内容管理',
                  'controller'=> 'News',
                  'controller_title'=> '新闻管理',
                  'sort'      => 1,
              ],
    ],
];

$menuGenerate = new Qscmf\Utils\MigrationHelper\MenuGenerate();
$menuGenerate->insertAll($this->nodeData);

// 撤销
$menuGenerate->insertAllRollback($this->nodeData);
```

## 

## RefModel

从关联表预提取关联数组（解决N+1循环取数导致数据库频繁访问的问题）

#### API

1. fill($data_ents, $key, $extra_where = null)
   
   > 用处：给关联对象填充关联值
   > 
   > data_ents 关联数据源
   > 
   > key 从关联数据源提取关联表数据的键值
   > 
   > extra_where 附加查询条件

2. pick($value, $field = null, $callback = null)
   
   > 用处：从关联对象中提取值
   > 
   > value 关联数据源的对应数据，与fill方法的key对应
   > 
   > field 指定提取的字段，默认null，表示提取所有字段
   > 
   > callback 回调函数，接收一个参数，为关联数据中，field指定的数据， return 作为最终提取数据

3. pickAll()
   
   > 用处： 从关联对象中提取全部数据

#### 用法

一般用法

```php
$reader_ents = D("Reader")->where(['status' => 1])->select();
$school_ref = new RefModel(D('School'), 'id'); //设置目标表的model类  设置目标表的关联id
$school_ref->fill($reader_ents, 'school_id'); // 通过$reader_ents的school_id预提取关联表的关联数据

foreach($reader_ents as &$v){
    $v['school_name'] = $school_ref->pick($v['school_id'], 'school_name'); //从预提取到的关联数据拿目标值, 当第二个参数传递null，则会返回包含表全部字段的数组
}
```

高级用法

```php
//通过传递闭包函数来获取更复杂的关联数据
//如用一般用法只能获取到读者头像id对应的本地图片路径，如果还需要进一步获取imageproxy的代理地址，则可传递一个闭包函数实现
$reader_ents = D("Reader")->where(['status' => 1])->select();
$pic_ref = new RefModel(D('FilePic'));
$pic_ref->fill($reader_ents, 'avatar'); 

foreach($reader_ents as &$v){
    $v['avatar_url'] = $pic_ref->pick($v['avatar'], null, function($file_ent){
        return \Qscmf\Utils\Libs\Common::imageproxy('100x100', $file_ent);
    }); //闭包函数接收由第一二个参数决定的提取值，这里的imageproxy可以接收一条file_pic的数据库记录来拼接出图片的代理地址，因此我们可以第二个参数传递null来简化数据库的查询次数。
}
```

```php
//跨两张表查询数据
//apply是读者申请表, return_reason表是申请退回原因定义表, 要查对着被退回的原因
//status = 2是退回
$reader_ents = D("Reader")->where(['status' => 2])->select();

$apply_ref = new RefModel(D('Apply'), 'reader_id');
$apply_ref->fill($reader_ents, 'id'); 

$return_ref = new RefModel(D('ReturnReason'));
$return_ref->fill($apply_ref->pickAll(), 'reason_id');

foreach($reader_ents as &$v){
    $v['return_reason_text'] = $apply_ref->pick($v['id'], 'reason_id', function($reason_id) use ($return_ref){
        return $return_ref->pick($reason_id, 'desc');
    }); 
}
```

## 

## RedisLock

基于Redis改造的悲观锁

+ 先获取锁再执行业务逻辑，执行结束释放锁。
+ 保证同一个方法的并发重复操作请求只有一个请求可以获取锁，在不进行高延迟事务处理的场景下可以使用。
+ 若只要一次获取锁成功，其它等待的请求可以通过callback处理，提前退出
  + ```text
    情景举例
    接口返回的数据使用了缓存，当发生缓存雪崩时，大量的请求就会直接发送到MySql，会导致MySql压力过大，响应缓慢。
    解决方案是，在发生缓存雪崩时，使用悲观锁，只有一个请求能从MySql中获取数据，设置好缓存值后，其它请求不需要获取锁，直接返回缓存值即可。
    ```

##### lock

```blade
该方法可以获取锁

参数 
$key 名称
$expire 过期时间 单位为秒
$timeout  循环取锁时间 单位为秒，默认为0
$interval 取锁失败后重试间隔时间 单位为微秒，默认为100000
callback  若回调返回有效值，则提前退出取锁流程
          回调返回数据类型为数组，[$flag,$result]，若$flag为true，则返回$res，否则继续执行取锁流程

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
    $redis_lock = \Qscmf\Utils\Libs\RedisLock::getInstance();
    $is_lock = $redis_lock->lock('exec_shell_lock_key', 60);
    $is_lock === false && $this->error('请一分钟后再操作');

    shell_exec('ll >/dev/null');

    $redis_lock->unlock('exec_shell_lock_key');
}
```

##### lockWithCallback
```blade
回调值无效则取锁

参数 
key       名称
expire    过期时间 单位为秒
callback  若回调返回有效值，则提前退出取锁流程
           回调返回数据类型为数组，[$flag,$result]，若$flag为true，则返回$result，否则继续执行取锁流程
timeout   循环取锁时间 单位为秒
interval  取锁失败后重试间隔时间 单位为微秒

返回值为数组
第一个值为锁情况，锁成功返回true 锁失败返回false，若不存在则为null
第二个值为回调返回值，若不存在则为null
两个值只会存在其中一种
```
##### 代码示例

```php
public function getRes(){
    $cache_data = S("api_cache_data");
    if(!$cache_data){
        $redis_lock_cls = new RedisLock();
        list($is_lock, $cache_data) = $redis_lock_cls->lockWithCallback($this->genLockKey(),30, [$this,"fetchCacheData"],30, 100000);
        if ($is_lock === false){
            $res = ['info' => "系统繁忙，请稍后再试", 'status' => 0];
        }elseif($is_lock === true){
            // 业务逻辑           
            $data = []; // 获取数据库数据
            $res = ['info' => "成功", 'status' => 1, 'data' => $data];
            S("api_cache_data", json_encode($res));
            $redis_lock_cls->unlock($this->genLockKey());
        }else{
            $res = $cache_data;
        }
    }else{
        $res = $cache_data;
    }

    return $res;
}

protected function genLockKey():string{
    return 'api_redis_lock';
}

public function fetchCacheData(){
    $data = S("api_cache_data");
    $flag = is_array($data)
    return [$flag, $data];
}
```

## 

## 共享排他锁

排他锁一般是基于某个key，只有一个进程可以持有。与其他的key的锁毫不相关。但有些业务场景，如基金配捐业务，基金有可配捐总额，为了避免并发问题，产生超出上总额的配捐数据，会对基金id上排他锁。平台也有个日配捐上限的设置，所有配捐共享这个上限值。如果此时管理员去修改日配捐上限，安全的做法应该要上一个配捐的总锁，避免在修改的过程中刚好有配捐业务导致数据错乱。此时这个总锁就要求和各个基金锁存在排他关系才能满足需求。共享排他锁就是为了满足这种需求而产生的工具。

![流程图](https://github.com/quansitech/files/blob/master/share_exclusive_lock.png)

#### API

```php
//排他锁
$lock = new ExclusiveLock(string $key, in $expire = 5, int $timeout = 0)

//上锁
$lock->lock();

//解锁
$lock->unlock();

//注册共享锁
$lock->register(SharedLock $lock);


//共享锁
$share_lock = new SharedLock(string $key, int $type = self::TYPE_SHARED);
```



#### 用法

```php
//创建排他锁
$all_lock = new ExclusiveLock('all_lock', 3600);
//注册共享锁，并且该锁是独占类型。意思只要该排他锁生成，其余用了相同key的共享锁则不能产生
$all_lock->register(new SharedLock('single_lock', SharedLock::TYPE_EXCLUSIVE));
$all_lock->lock();
sleep(10);
$all_lock->unlock();


//创建排他锁2
$fund_lock = new ExclusiveLock('single_lock_1', 3600);
//注册共享类型的共享锁，意思是相同key的共享类型共享锁可以存在多个，但和独占类型的共享锁互斥
$fund_lock->register(new SharedLock('single_lock', SharedLock::TYPE_SHARED));
$fund_lock->lock();
sleep(10);
$fund_lock->unlock();

//创建排他锁3
$fund_lock = new ExclusiveLock('single_lock_2', 3600);
$fund_lock->register(new SharedLock('single_lock', SharedLock::TYPE_SHARED));
$fund_lock->lock();
sleep(10);
$fund_lock->unlock();
```

简单说明下上面的代码，当all_lock类型的锁产生后，由于该锁同时持有独占类型的single_lock。那么single_lock_1和single_lock_2创建时将会发生堵塞，直到all_lock释放为止。反过来，如果single_lock_1先生成了，那么all_lock创建时也会发生堵塞。

single_lock_1和single_lock_2由于持有single_lock的共享类型锁，所以它们之间不会发生堵塞。





## imageproxy

[imageproxy](https://github.com/willnorris/imageproxy) 是个图片裁剪、压缩、旋转的图片代理服务。框架集成了imageproxy全局函数来处理图片地址的格式化，通过.env来配置地址格式来处理不同环境下imageproxy的不同配置参数

+ env的地址格式配置
  
  ```blade
  IMAGEPROXY_URL={schema}://{domain}/ip/{options}/{remote_uri}
  ```

+ 占位符替换规则
  
  ```
  占位符用{}包裹
  schema 当前地址的协议类型 http 或者 https
  domain 当前网站使用的域名
  options 图片处理规则 https://godoc.org/willnorris.com/go/imageproxy#ParseOptions
  remote_uri 代理的图片uri，如果外网图片，该占位符会替换成该地址，否则是网站图片的uri
  path 网站图片的相对地址，如 http://localhost/Uploads/image/20190826/5d634f5f6570f.jpeg，path则为Uploads/image/20190826/5d634f5f6570f.jpeg
  ```

+ imageproxy全局函数
  
  ```php
  // imageproxy图片格式处理
  // options 图片处理规则
  // file_id 图片id，若为ulr，则返回该url, 也可以是file_pic的数据库行记录（省略数据库查询操作）
  // cache 默认为空，不开启缓存，否则可设置缓存时间，单位秒
  // return 返回与.env配置格式对应的图片地址
  \Qscmf\Utils\Libs\Common::imageproxy($options, $file_id, $cache)
  ```

如 IMAGEPROXY_URL={schema}://{domain}/ip/{options}/{remote_uri}
\Qscmf\Utils\Libs\Common::imageproxy('100x150', 1)
返回地址 http://localhost/ip/100x150/http://localhost/Uploads/image/20190826/5d634f5f6570f.jpeg

如 IMAGEPROXY_URL={schema}://{domain}/ip/{options}/{path} (这种格式通常配合imageproxy -baseURL使用)
返回地址 http://localhost/ip/100x150/Uploads/image/20190826/5d634f5f6570f.jpeg

```
+ 远程imageproxy代理

有些项目，需要采用远程的一台服务器作为图片代理服务，此时可通过在.env设置IMAGEPROXY_REMOTE来设置远程服务器的域名
```php
//.env文件
IMAGEPROXY_URL={schema}://{domain}/{options}/{remote_uri}
IMAGEPROXY_REMOTE=http://www.test.com

//imageporxy生成的地址
$url = \Qscmf\Utils\Libs\Common::imageproxy('1920x540',$banner_id);
echo $url;
//http://www.test.com/1920x540/http://localhost/Uploads/images/xxxx.jpg
```

## Common 公用函数

+ imageproxy
见imageproxy部分

+ cached
开箱即用的缓存工具，内部实现防缓存雪崩机制
```php
//参数说明
//第一个参数为匿名函数，实现获取数据的业务逻辑
//第二个参数为缓存过期时间，单位秒
//第三个参数为缓存key，若为空则使用匿名函数的参数作为key
//第四个参数为分组标识，若不为空，则产生的key将会归入该分组，使用clearCachedGroup方法可以清除该分组的缓存

//用法举例
//以下方法要从数据库读取数据，如果该页面是热点页，则无法承载太多的并发请求，需要针对其进行缓存
$ent = D('Project')->getOneProject($map);

//使用Common::cached方法快速实现该工作
//以下为改造后效果
//生成缓存函数便于重复使用
$project_cached = Common::cached(function($map){
    $ent = D('Project')->getOneProject($map);
    return $ent;
}, 60);

//使用生成的缓存函数完成数据获取和缓存的工作
$ent = $project_cached($map);

//指定缓存key举例
$project_cached = Common::cached(function($map){
    $donate_amount = D('ProjectDonate')->donateAmount($map);
    return $donate_amount;
}, 3600, 'project_donate_amount_' . $project_id, 'project_donate_amount');

//指定缓存key后，可实现对缓存值不落盘更新
$redis = Cache::getInstance('redis');
//incrByFloat 方法必须升级到think-core v13.3.0以上版本才能使用
$redis->incrByFloat("project_donate_amount_{$project_id}", $donate_amount);

//清除分组缓存
Common::clearCachedGroup('project_donate_amount');
```


```


## AuthNodeGenerate

```text
生成权限点

使用权限点来限制字段、按钮的展示时，一般格式为：
模块.控制器.方法名，如 admin.user.add
```

#### 用法

+ 新增权限点

若模块、控制器不存在则自动新增，它们的标题默认为名称，可以根据需要自定义标题。

```php
// 参数说明
// $module_name 模块名
// $controller_name 控制器名
// $action_name 权限点名
// $title 权限点标题
// $pid 父节点，若为空则根据模块名、控制器名查找

Qscmf\Utils\MigrationHelper\AuthNodeGenerate::addAuthNode('admin', 'user', 'add', '新增');
Qscmf\Utils\MigrationHelper\AuthNodeGenerate::addAuthNode('admin', 'user', 'edit', '编辑');
```

```php
// 修改模块、控制器标题
Qscmf\Utils\MigrationHelper\AuthNodeGenerate::addAuthNode(['UserAdmin','用户'], ['user', '用户管理'], 'add', '新增');
```

+ 删除权限点

```php
// 参数说明
// $module_name 模块名
// $controller_name 控制器名
// $action_name 权限点名，若为空则删除该控制器下的所有权限点

// 只删除一个权限点
Qscmf\Utils\MigrationHelper\AuthNodeGenerate::deleteAuthNode('admin', 'user', 'add');
Qscmf\Utils\MigrationHelper\AuthNodeGenerate::deleteAuthNode('admin', 'user', 'edit');
```

```php
// 删除控制器下所有权限点
Qscmf\Utils\MigrationHelper\AuthNodeGenerate::deleteAuthNode('admin', 'user', '');
```

## AccessGenerate

AccessGenerate 类是一个迁移助手工具，用于向数据库中插入或删除指定角色的权限点。

### 方法列表

#### `add(int $role_id, string $module, string $controller, string $action) : void`

功能：向数据库中插入指定角色的权限点。

- `$role_id`（整数类型）：角色ID，表示需要插入权限点的角色。
- `$module`类型）：模块名称，表示权限点所属的模块。
- `$controller`（字符串类型）：控器名称，表示权限点所的控制器。
- `$action`（字符串类型）权限点名称，表示具权限点。

返回值：无。

#### `del(int $role_id, string $module, string $controller, string $action) : void`

功能：从数据库中删除指定角色的权限点。

参数：

- `$role_id`（整数类型）：角色ID，表示需要删除权限点的角色。
- `$module`字符串类型）：模块名称，表示需要删除权限点的模块。
- `$controller`（字符串类型）控制名称，表示需要删除权限点的控制器。
- `$action`（字符串类型）：权限点名称，表示具要删除的权限点。

返回值：无。

## DBComment
```text
给数据表及其字段添加/修改注释
```

#### 用法

##### buildChangeSql
```text
根据注释映射数组生成一个更改数据表及其字段注释的DDL
```
```php
\Qscmf\Utils\Libs\DBComment::buildChangeSql($comment_mapping);

// 参数说明
// $comment_mapping结构为
// ['数据表名称'=>['name'=>'数据表名称','comment'=>'数据表注释', 'column' =>['字段名1'=>'字段1注释','字段名2'=>'字段2注释']]]

```

```php
$comment_mapping = [
        'migrations' => [
            'name' => 'migrations',
            'comment' => '数据迁移表',
            'column' => [
                'id' => '流水号，主键',
                'migration' => '文件名',
                'before' => '运行前执行情况',
                'run' => '脚本执行情况',
                'after' => '运行前执行情况',
                'batch' => '批次',
            ]
        ],
        'qs_access' =>
            [
                'name' => 'qs_access',
                'comment'=> '用户组关联权限点表',
                'column'=>  [
                    'role_id' => '用户组id,qs_role主键',
                    'node_id' => '权限点id,qs_node主键',
                    'level' => '权限点类型',
                    'module' => '权限点名称',
                ],
            ],
];

\Qscmf\Utils\MigrationHelper\DBComment::buildChangeSql($comment_mapping);

// 输出结果为一下内容，可使用information_schema.columns数据表核对字段定义部分
/**
ALTER TABLE
    `migrations` COMMENT = '数据迁移表',
    CHANGE COLUMN `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水号，主键',
    CHANGE COLUMN `migration` `migration` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件名',
    CHANGE COLUMN `before` `before` TINYINT(1) NOT NULL COMMENT '运行前执行情况',
    CHANGE COLUMN `run` `run` TINYINT(1) NOT NULL COMMENT '脚本执行情况',
    CHANGE COLUMN `after` `after` TINYINT(1) NOT NULL COMMENT '运行前执行情况',
    CHANGE COLUMN `batch` `batch` INT NOT NULL COMMENT '批次';
ALTER TABLE
    `qs_access` COMMENT = '用户组关联权限点表',
    CHANGE COLUMN `role_id` `role_id` SMALLINT UNSIGNED NOT NULL COMMENT '用户组id,qs_role主键',
    CHANGE COLUMN `node_id` `node_id` SMALLINT UNSIGNED NOT NULL COMMENT '权限点id,qs_node主键',
    CHANGE COLUMN `level` `level` TINYINT NOT NULL COMMENT '权限点类型',
    CHANGE COLUMN `module` `module` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '权限点名称';
**/
 ```

## Scroller 滚动分页工具类

滚动分页是基于每次获取的最后一条数据，去获取下一批数据，只要条件里包含里唯一键，就可以保证数据不重复，适用于数据量大，数据不断增加的场景。

通过page的分页方式，当搜索到比较大的页码时，会导致查询时间过长，甚至超时，滚动分页可以避免这种情况。

当数据并发量大时，滚动分页可以避免数据重复，保证用户体验。

#### 用法

```php
public function gets(){
    $get_data = I('get.');
    $count = $get_data['count'] ?: C("HOME_PER_PAGE_NUM",null, 20);
    
    $order = 'sort asc,id desc';
    $scroller = new Scroller($order);
    
    $map = [];
    $map['status'] = \Gy_Library\DBCont::NORMAL_STATUS;

    //检查参数里是否包含下一次的查询条件，有则调用applyLastCondition方法构造查询条件
    if(isset($get_data['last_condition']) && !qsEmpty($get_data['last_condition'])){
        $scroller->applyLastCondition($map, $get_data['last_condition']);
    }

    $list = D("Gift")->getGiftList($map, 1, $row_count, $order);
    $res = [
        'list' => $list,
        'last_condition' => qsEmpty($list) ? "" : $scroller->toLastCondition($list[count($list) - 1]), //toLastCondition从最后一条数据生成下一次查询的条件
    ];
    
    return new Response("获取成功", 1, $res);
}

```

## AnalysisCUD

经常遇到一些场景，需要对一堆数据进行批量操作。前端操作完，提交到后端是一堆处理后的数据。里面混杂着要新增，更新，还可能隐含了要删除的数据。

通常我们会根据提交上来的数据id，与数据库的数据进行比较，来判断是新增还是更新，还是删除。这个类就是为了简化这个操作。

#### 用法

```php
$db_data = [
    ['id' => 1, 'name' => 'item1'],
    ['id' => 2, 'name' => 'item2'],
    ['id' => 3, 'name' => 'item3']
];

$new_data = [
    ['name' => 'item4'],       // 新增
    ['id' => 2, 'name' => 'item2_updated'], // 更新
    ['id' => 4, 'name' => 'item4'], // 新增
];

$cud = new AnalysisCUD($db_data, $new_data);
$result = $cud->analysis();

print_r($result);

/*
Array
(
    [to_insert] => Array
        (
            [0] => Array
                (
                    [name] => item4
                )

            [1] => Array
                (
                    [id] => 4
                    [name] => item4
                )

        )

    [to_update] => Array
        (
            [0] => Array
                (
                    [id] => 2
                    [name] => item2_updated
                )

        )

    [to_delete] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [name] => item1
                )

            [1] => Array
                (
                    [id] => 3
                    [name] => item3
                )

        )

)
*/
```