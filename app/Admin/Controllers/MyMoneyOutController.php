<?php

namespace App\Admin\Controllers;

use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\MoneyOut;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MyMoneyOutController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '放贷记录';

    protected $checkstatus = [0=>'待审核',1=>'审核通过',2=>'审核拒绝'];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MoneyOut());

        if(self::isEmployee()){
            $grid->model()->where('employee_id',self::current_id());
        }

        $grid->disableActions();
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->column('id', __('ID'))->sortable();
        $grid->column('client_id', '客户姓名')->using(Client::getAllClients());
        $grid->column('employee_id', '所属员工')->using(User::Employees());
        $grid->column('out_id','放贷产品');
        $grid->column('out_money','放贷金额');
        $grid->column('fee','居间服务费');
        $grid->column('pay_type','还款方式')->using(CrmConfig::getKeyValue('money_back_type'));
        $grid->column('pay_monthly','月还款金额');
        $grid->column('pay_count','期数');
        $grid->column('out_at','放贷日期');
        $grid->column('check_status','审核状态')->using($this->checkstatus);
        $grid->column('dianzihuidan','银行电子回单');

        $grid->filter(function (Grid\Filter $filter){
            $filter->column(1/2,function ($filter){
                $filter->equal('client_id','客户姓名')->select(Client::getAllClients());
                $filter->equal('employee_id','销售人员')->select(User::Employees());
                $filter->equal('check_status','审核状态')->select($this->checkstatus);
            });

            $filter->column(1/2,function ($filter){
                $filter->equal('pay_type','还款方式')->select(CrmConfig::getKeyValue('money_back_type'));
                $filter->between('out_at','放贷日期')->date();
            });

            $filter->disableIdFilter();
        });

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
