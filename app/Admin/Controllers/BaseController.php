<?php
namespace App\Admin\Controllers;

use App\Admin\Actions\Call;
use App\Admin\Actions\GiveOther;
use App\Admin\Extensions\Tools\ExcelImport;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class BaseController extends AdminController
{
    protected $sh_status = [0=>'待审核','通过','驳回'];

    public function permission()
    {
        $permission = [
            'query',
            'create',
            'import',
            'export',
            'forward',
            'edit',
            'delete',
        ];

        DB::table('admin_menu')->where('parent_id','>',0)->get();
    }


    public static function isManager()
    {
        return Admin::user()->isRole('manager');
    }

    public static function isSuper()
    {
        return Admin::user()->isRole('administrator');
    }

    public static function current_id()
    {
        return Admin::user()->id;
    }

    /**
     * @param Grid $grid
     * @param bool $import_enable   是否允许导入
     * @param bool $forward_enable  是否允许转交
     * @param bool $call_enable     是否允许拨号
     * @param bool $secret_enable   是否只查看自己的keh
     * @return Grid
     */
    public static function permissions(Grid $grid, $import_enable = false, $forward_enable = false, $call_enable = false, $secret_enable = true)
    {

        $uri = str_replace('admin/','',Route::current()->uri);

        // 查询权限
        if(! Admin::user()->can($uri.'.query')){
            $grid->disableFilter();
        }
        // 新增权限
        if(! Admin::user()->can($uri.'.create')){
            $grid->disableCreateButton();
        }
        // 导入权限
        if(Admin::user()->can($uri.'.import') && $import_enable){
            $grid->tools(function ($tools){
                $tools->append("<a class='btn btn-sm btn-info' href='/upload/files/导入格式.xlsx' target='_blank' download='导入格式'>下载数据导入格式</a>");
                $tools->append(new ExcelImport());
            });
        }
        // 导出权限
        if(! Admin::user()->can($uri.'.export')){
            $grid->disableExport();
        }
        // 转交权限
        if(Admin::user()->can($uri.'.forward') && $forward_enable){
            $grid->batchActions(function ($batch){
                $batch->add(new GiveOther());
            });
        }
        // 编辑权限
        if(! Admin::user()->can($uri.'.edit')){
            $grid->actions(function ($actions){
                $actions->disableEdit();
            });
        }
        // 删除权限
        if(! Admin::user()->can($uri.'.delete')){
            $grid->actions(function ($actions){
                $actions->disableDelete();
            });
            $grid->batchActions(function ($batch){
                $batch->disableDelete();
            });
        }

        // 允许拨号
        if($call_enable == true){
            $grid->actions(function ($actions){
                $actions->add(new Call($actions->row['id']));
            });
        }

        // 数据保护，只能查看自己的
        if($secret_enable == true){
            $grid->model()->where('employee_id', self::current_id());
        }

        // 默认设置  关闭查看
        $grid->actions(function ($actions){
            $actions->disableView();
        });





        return $grid;

    }

}
