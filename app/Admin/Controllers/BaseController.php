<?php
namespace App\Admin\Controllers;

use App\Admin\Actions\Call;
use App\Admin\Actions\GiveOther;
use App\Admin\Extensions\Tools\ExcelImport;
use App\Imports\SeaImport;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Media\MediaManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

class BaseController extends AdminController
{


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

    public static function isEmployee()
    {
        return Admin::user()->isRole('employee');
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
        if($call_enable){
            $grid->actions(function ($actions){
                $actions->add(new Call($actions->row['id']));
            });
        }

        // 数据保护，只能查看自己的
        if($secret_enable){
            $grid->model()->where('employee_id', self::current_id());
        }

        // 默认设置  关闭查看
        $grid->actions(function ($actions){
            $actions->disableView();
        });

        return $grid;

    }

}
