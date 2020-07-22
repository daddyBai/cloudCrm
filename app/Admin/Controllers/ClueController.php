<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchDistribution;
use App\Admin\Actions\Call;
use App\Admin\Actions\Change;
use App\Admin\Actions\GiveOther;
use App\Admin\Actions\NeedHelp;
use App\Admin\Extensions\Tools\ExcelImport;
use App\Admin\Traits\tabMenu;
use App\Exports\ClientExport;
use App\Models\China;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\Manager;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/**
 * Class ClueController 线索
 * @package App\Admin\Controllers
 */
class ClueController extends BaseController
{
    /**
     * Title for born resource.
     *
     * @var string
     */
    protected $title = '线索';


    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Client());

        $grid->exporter(new ClientExport());

        $grid = $this->permissions($grid, true, true, true);

        $grid->model()
            ->whereIn('true_client',[0,2])
            ->orderBy('created_at','desc');

        $grid->column('id',__('ID'))->sortable();
        $grid->column('name','姓名')->display(function ($model){
            return "<a href='/admin/clue/$this->id/edit'>$model</a>";
        });
        $grid->column('mobile','手机号(点击拨号)')->display(function ($model){
            return "<i class='fa fa-phone-square'></i>&nbsp;&nbsp;<a href='/admin/dial/call/$this->id'>$model</a>";
        });
        $grid->column('employee_id','销售人员')->using(User::Users());
        $grid->column('status','客户类型')->using(CrmConfig::getKeyValue('client_status'));
        $grid->column('employee_status','跟进状态')->using(CrmConfig::getKeyValue('follow_status'));
        $grid->column('last_updated_at','最后跟进时间')->display(function ($model){
            return ! empty($model) ? Carbon::parse($model)->diffForHumans() : '';
        });

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

        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->add(new Call($actions->row['id']));
            $actions->disableView();
        });

        $grid->batchActions(function ($batch){
            $batch->add(new Change());
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

        $show->field('name','姓名');
        $show->field('sex','性别')->using([1=>'男',2=>'女']);
        $show->field('mobile','手机号')->filter();
        $show->field('status','客户状态')->using(CrmConfig::getKeyValue('client_status'));
        $show->field('marriage','婚姻状况')->using(Client::marriage);
        $show->field('email','邮箱');
        $show->field('education','学历')->using(Client::education);
        $show->field('age','年龄');

        $show->field('china_id','身份证号')->as(function ($model){
            return '不可查看';
        });
        $show->field('china_id_valid','身份证有效期');
        $show->field('current_prov','当前地址')->as(function ($model){
            return China::change($model)
                .' - '.China::change($this->current_city)
                .' - '.China::change($this->current_area)
                .' - '.$this->current_street;
        });
        $show->field('born_prov','籍贯地址')->as(function ($model){
            return China::change($model)
                .' - '.China::change($this->born_city)
                .' - '.China::change($this->born_area)
                .' - '.$this->born_street;
        });
        $show->field('income','本人月收入(¥)');
        $show->field('family_in','家庭月收入(¥)');
        $show->field('family_out','家庭月支出(¥)');
        $show->field('shebao','社保')->using(CrmConfig::SWITCH_STATES_YM);
        $show->field('shebao_in','个人月缴存额（¥）：');
        $show->field('gongjijin','公积金（¥）：')->using(CrmConfig::SWITCH_STATES_YM);
        $show->field('gongjijin_in','个人月缴存额（¥）：');
        $show->field('company','单位名称');
        $show->field('company_address','单位地址');
        $show->field('company_type','行业类型');
        $show->field('company_belong','单位性质')->using(Client::company_type);
        $show->field('company_post','公司职位')->using(Client::company_post);
        $show->field('debt','剩余负债');
        $show->field('monthly_debt','月还款额度');
        $show->field('note','备注');
        $show->field('employee_id','所属销售')->using(User::Employees());
        $show->field('employee_status','跟进状态')->using(CrmConfig::getKeyValue('follow_status'));
        $show->field('house','房产数量');
        $show->field('car','车产数量');
        $show->field('updated_at','更新时间');
        $show->field('created_at','创建时间');



        return  $show;
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
    public function form()
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

        // 线索
        $form->hidden('true_client')->value(0);

        return $form;
    }
}
