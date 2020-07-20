<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchDistribution;
use App\Models\China;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\Manager;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;

class AllClientController extends BaseController
{
    /**
     * Title for born resource.
     *
     * @var string
     */
    protected $title = '我的客户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Client());


        if(self::isManager()){

        }else if(self::isEmployee()){        // 针对于销售人员的规则       禁止多选、创建
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->actions(function ($actions){
                $actions->disableView();
                $actions->disableDelete();
            });

            // 销售人员只能看自己管理的客户
            $grid->model()->where('employee_id',Admin::user()->id);
        }else if(self::isManager()){
            $grid->model()->whereIn('employee_id',array_keys(Manager::myGroup()));
        }

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name',__('name'))->modal('详细信息',function ($model){
            $box = new Box('个人信息','内容');
            $box->removable();
            $box->collapsable();
            $box->style('info');
            $box->solid();
            $box->scrollable();
            return $box;
        })->filter();
        $grid->column('sex','性别')->using([1=>'男',2=>'女'])->filter([1=>'男',2=>'女']);
        $grid->column('mobile','手机号')->filter();
        $grid->column('status','客户状态')->using(CrmConfig::getKeyValue('client_status'))->filter(CrmConfig::getKeyValue('client_status'));
        $grid->column('marriage','婚姻状况')->using(Client::marriage)->filter(Client::marriage);
        $grid->column('email','邮箱');
        $grid->column('education','学历')->using(Client::education)->filter(Client::education);
        $grid->column('age','年龄')->filter('range');

        // 针对于销售主管和超管的开放权限
        if(self::isManager() || self::isSuper()){
            $grid->column('employee_id','负责人')->using(User::Employees())->filter(User::Employees());
        }

        $grid->column('employee_status','跟进状态')->using(CrmConfig::getKeyValue('follow_status'))->filter(CrmConfig::getKeyValue('follow_status'));
        $grid->column('updated_at','最后更新时间')->display(function ($model){
            if(empty($model)){
                return '';
            }
            $lastUpdateTime = time() - strtotime($model);
            $lastUpdateTime = ceil($lastUpdateTime/60/60/24);
            if($lastUpdateTime > 6){
                $lastUpdateTime = "<span style='color: red'>$lastUpdateTime 天前</span>";
            }else{
                $lastUpdateTime = "<span style='color: green'>$lastUpdateTime 天</span>";
            }
            return $lastUpdateTime;
        })->filter('range','date');

        $grid->filter(function ($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->column(1/3,function ($filter){
                $filter->like('name','客户姓名');
                $filter->in('employee_status','跟进状态')->multipleSelect(CrmConfig::getKeyValue('follow_status'));
            });
            $filter->column(1/3,function ($filter){
                $filter->equal('sex','性别')->select([1=>'男',2=>'女']);
            });
            $filter->column(1/3,function ($filter){
                $filter->equal('mobile','手机号码');
                $filter->equal('employee_id','员工')->select(User::Employees());
            });
        });



        $grid->batchActions(function ($batch){
            $batch->add(new BatchDistribution());
            $batch->disableDelete();
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

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
    }

}
