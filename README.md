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
> $process = new \Qscmf\Utils\MigrationHelper\CmmProcess();
> //timeout为程序的超时退出时间，默认60秒
> $process->setTimeOut(100)->callTp('/var/www/move/www/index.php', '/home/index/test');
> ```

### ConfigGenerator
  
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

### MenuGenerate
生成菜单和节点列表
自动处理menu和node的关系

#### 用法
+  生成top_menu为平台的菜单和节点列表
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

+  通过controller_title字段可自定义控制器title，默认为controller
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

### RefModel

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
    $redis_lock = \Qscmf\Utils\Libs\RedisLock::getInstance();
    $is_lock = $redis_lock->lock('exec_shell_lock_key', 60);
    $is_lock === false && $this->error('请一分钟后再操作');

    shell_exec('ll >/dev/null');
    
    $redis_lock->unlock('exec_shell_lock_key');
}
```

### imageproxy
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

### AuthNodeGenerate
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

// 删除控制器下所有权限点
Qscmf\Utils\MigrationHelper\AuthNodeGenerate::deleteAuthNode('admin', 'user', '');
```
