<?php

use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    # 默认进入数据报表
    $router->get('/', 'StatController@index')->name('home');

    /**
     * 线索
     */
    $router->resource('/clue','ClueController');                # 线索 ok
    $router->resource('/records','RecordsController');          # 跟进记录
    $router->resource('/assets','AssetsController');            # 客户资产
    $router->resource('/files','ClientFileController');         # 客户文件
    $router->resource('/needHelp','NeedHelpController');        # 协助记录

    $router->resource('dial','DialController');                 # 呼叫记录

    /**
     * 客户
     */
    $router->resource('/clients','ClientController');
    $router->resource('/records','RecordsController');          # 跟进记录
    $router->resource('/assets','AssetsController');            # 客户资产
    $router->resource('/files','ClientFileController');         # 客户文件
    $router->resource('/needHelp','NeedHelpController');        # 协助记录
    $router->resource('moneyOut','MoneyOutController');         # 贷款管理
    $router->resource('/feedback','FeedBackController');        # 回访

    $router->resource('moneyOutCheck','MoneyOutCheckController');       # 客户成交审核

    # 系统设置
    $router->resource('crmConfig','CrmConfigController');      # 数据字典
    $router->resource('syslog','SysLogController');

    # 客户管理
    $router->resource('/myClient','MyClientController');        # 我的客户
    $router->resource('/allClient','AllClientController');      # 全部客户
    $router->resource('/sea','SeaController');                  # 公海

    # 客户线索

    $router->resource('/threepart','ThreePartController');

    $router->put('/roles/update/{id}','RoleController@updateForView');
    $router->resource('roles','RoleController');
    $router->resource('permissions','PermissionController');
    $router->resource('users','UserController');


    # 业绩管理
    $router->resource('/mission','MissionController');

    # 部门管理
    $router->resource('/department','DepartmentController');
    $router->resource('/myDepartment','MyDepartmentController');

    # 贷款审核


    $router->resource('myMoneyOut','MyMoneyOutController');             # 我的贷款

    # 回访

    $router->resource('/myFeedBack','MyFeedBackController');

    # 导入数据
    $router->any('/cardnewsimport', 'SeaController@import');

    # 数据统计
    $router->resource('stat','StatController');

    # 清空缓存
    $router->get('/refreshRedis','CrmConfigController@refreshRedis');

    # 拨号
    $router->get('dial/call/{id}','DialController@dial');

    # 测试
    $router->any('/test',function () {
        foreach (range(0,15) as $value){
            $id[] = random_int(1,500);
        }
       \App\Models\Client::query()
           ->whereIn('id',$id)
           ->update([
               'employee_id'=>6,
               'true_client'=>1,
               'real_finished'=>1,
               'status'=>1
               ]);

//        $day = random_int(1,5);
//
//        \App\Models\Client::query()->where('true_client',1)
//            ->update(['last_updated_at'=>Carbon::now()->subDays($day)->toDateTimeString()]);
    });
});
