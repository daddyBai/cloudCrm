<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchDistribution;
use App\Admin\Actions\Sea;
use App\Admin\Traits\tabMenu;
use App\Exports\ClientExport;
use App\Imports\Excels;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\Department;
use App\Models\Manager;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户列表';


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
            ->where('true_client',1)
            ->orderBy('created_at','desc');
        $is_leader = true;
        if(Admin::user()->is_leader ==1){
            $ids = User::query()->where('department_id',Admin::user()->department_id)->pluck('id')->toArray();
            $grid->model()->whereIn('employee_id',$ids);
            $is_leader=false;
        }
        $grid = $this->permissions($grid, true, true, true,$is_leader);

        $grid->column('id',__('ID'))->sortable();
        $grid->column('name','姓名')->display(function ($model){
            return "<a href='/admin/client/$this->id/edit'>$model</a>";
        });
        $grid->column('mobile','手机号(点击拨号)')->display(function ($model){
            return "<i class='fa fa-phone-square'></i>&nbsp;&nbsp;<a href='/admin/dial/call/$this->id'>$model</a>";
        });
        $grid->column('employee_id','销售人员')->display(function ($model){
            return User::myTitle($model);
        });
        $grid->column('employee_status','跟进状态')->using(CrmConfig::getKeyValue('follow_status'));
//        $grid->column('last_updated_at','进入公海天数');
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



        $grid->batchActions(function ($batch){
            $batch->add(new Sea());
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
        $show = new Show(Client::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    public function edit($id, Content $content)
    {
        $client_name = Client::getAllClients()[$id];
        $content
            ->title($this->title().' - '.$client_name)
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->row(function ($row) use ($id){
                $row->column(12, tabMenu::tabMenuList($id));
            })
            ->row(function ($row) use ($id){
                $row->column(12,$this->form()->edit($id));
            });
        return $content;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Client);
        $form->select('status','客户类型')
            ->options(CrmConfig::getKeyValue('client_status'))
            ->required()->help('有明确意向的客户会自动进入客户审核区域');

        $form->fieldset('个人信息',function ($form){
            $form->text('name', '姓名')->required();

            $form->mobile('mobile', '手机号码')->required();
            $form->select('sex', '性别')->options([1 => '男', 2 => '女']);
            $form->number('age', '年龄');
            $form->select('marriage', '婚姻状况')->options(Client::marriage);
            $form->select('education', '学历')->options(Client::education);
            $form->email('email', '邮箱');
            $form->text('china_id', '身份证号');
            $form->datetime('china_id_valid', '身份证有效期');
            $form->distpicker([
                'current_prov' => '省份',
                'current_city' => '市',
                'current_area' => '区'
            ], '现居住地');
            $form->text('current_street', '当前居住-街道');
            $form->distpicker([
                'born_prov' => '省份',
                'born_city' => '市',
                'born_area' => '区'
            ], '籍贯地址');
            $form->text('born_street', '籍贯-街道');
        });
        $form->fieldset('资产信息',function ($form) {
            $form->currency('income','本人月收入')->symbol('￥');
            $form->currency('family_in','家庭月收入')->symbol('￥');
            $form->currency('family_out','家庭月支出')->symbol('￥');
        });
        $form->fieldset('社保信息',function ($form) {
            $form->switch('shebao','有无社保')->states(CrmConfig::SWITCH_STATES_YM);
            $form->currency('shebao_in','个人月缴存额')->symbol('￥');
        });
        $form->fieldset('公积金信息',function ($form) {
            $form->switch('gongjijin','有无公积金')->states(CrmConfig::SWITCH_STATES_YM);
            $form->currency('gongjijin_in','个人月缴存额')->symbol('￥');
        });
        $form->fieldset('工作信息',function ($form) {
            $form->text('company','单位名称');
            $form->text('company_address','单位地址');
            $form->text('company_type','行业类型');
            $form->select('company_belong','单位性质')->options(Client::company_type);
            $form->select('company_post','公司职位')->options(Client::company_post);
        });
        $form->fieldset('房产信息',function ($form){
            $form->hasMany('houses','房产信息',function (Form\NestedForm $form){
                $form->select('usefor','购房用途')->options(CrmConfig::getKeyValue('house_use_for'));
                $form->select('pay_type','付款方式')->options(CrmConfig::getKeyValue('money_pay_type'));
                $form->select('status','是否抵押')->options([1=>'是',2=>'否']);
                $form->text('value','房产价值');
                $form->text('house_size','房产面积');
            });
        });
        $form->fieldset('车产信息',function ($form){
            $form->hasMany('cars','车产信息',function (Form\NestedForm $form){
                $form->select('pay_type','付款方式')->options(CrmConfig::getKeyValue('money_pay_type'));
                $form->select('status','是否抵押')->options([1=>'是',2=>'否']);
                $form->text('value','车产价值');
                $form->date('buy_time','购入时间');
            });
        });
        $form->fieldset('负债信息',function ($form) {
            $form->currency('debt','未结清放贷余额')->symbol('￥');
            $form->currency('monthly_debt','每月还贷额')->symbol('￥');
        });
        $form->fieldset('资质情况备注',function ($form){
            $form->textarea('note','资质情况备注');
        });
        $form->divider();


        $form->select('employee_id','所属销售员')
            ->options(User::Users())->value(Admin::user()->id)->required()->help('注意不要选错了');
        $form->tools(function (Form\Tools $tools){
            $tools->disableDelete();
        });

        // 真实客户
        $form->hidden('true_client')->value(1);

        return $form;
    }

}
