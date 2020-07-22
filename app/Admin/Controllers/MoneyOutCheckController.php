<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Sea;
use App\Exports\ClientExport;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\MoneyOut;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MoneyOutCheckController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户成交审核';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Client());

        $grid->exporter(new ClientExport());

        $grid->model()
            ->where('status',1);
        $is_leader = true;
        if(Admin::user()->is_leader ==1){
            $ids = User::query()->where('department_id',Admin::user()->department_id)->pluck('id')->toArray();
            $grid->model()->whereIn('employee_id',$ids);

            $is_leader=false;
        }
        $grid = $this->permissions($grid, false, false, false,$is_leader);

        $grid->column('id',__('ID'))->sortable();
        $grid->column('name','姓名')->display(function ($model){
            return "<a href='/admin/clue/$this->id/edit'>$model</a>";
        });
        $grid->column('mobile','手机号');
        $grid->column('employee_id','销售人员')->display(function ($model){
            return User::myTitle($model);
        });
        $grid->column('employee_status','跟进状态')->using(CrmConfig::getKeyValue('follow_status'));
        $grid->column('created_at','创建时间');
        $grid->column('status','客户类型')->using(CrmConfig::getKeyValue('client_status'));

        $grid->column('last_updated_at','最后跟进时间')->display(function ($model){
            return ! empty($model) ? Carbon::parse($model)->diffForHumans() : '';
        });

        if(Admin::user()->is_leader ==1) {
            $grid->column('real_finished', '审核状态')->editable('select', $this->sh_status);
            $grid->column('employee_id', '归属销售')->using(User::Users());
        }else{
            $grid->column('real_finished', '审核状态')->using($this->sh_status);
        }
        $grid->filter(function ($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->column(1/2,function ($filter){
                $filter->like('name','客户姓名');
                $filter->equal('sex','性别')->select([1=>'男',2=>'女']);
                $filter->equal('employee_status','跟进状态')->select(CrmConfig::getKeyValue('follow_status'));
            });
            $filter->column(1/2,function ($filter){
                $filter->equal('mobile','手机号码');
                $filter->equal('employee_id','销售人员')->select(User::Employees());
            });
        });



        // 每页条数
        $grid->perPages([10,20,50,100,200,500,1000]);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MoneyOut::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MoneyOut);

        $form->display('id', __('ID'));
        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));

        return $form;
    }
}
