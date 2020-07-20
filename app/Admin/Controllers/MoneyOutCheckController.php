<?php

namespace App\Admin\Controllers;

use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\MoneyOut;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MoneyOutCheckController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '贷款审核';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($client_id = 0)
    {
        $grid = new Grid(new MoneyOut());

        if($client_id > 0){
            $grid->model()->where('client_id',$client_id);
        }
        $grid->disableActions();
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->column('id', __('ID'))->sortable();
        $grid->column('client_id', '客户姓名')->using(Client::getAllClients());
        $grid->column('client_id', '所属员工')->using(User::Employees());
        $grid->column('out_id','放贷产品');
        $grid->column('out_money','放贷金额');
        $grid->column('fee','居间服务费');
        $grid->column('pay_type','还款方式')->using(CrmConfig::getKeyValue('money_back_type'));
        $grid->column('pay_monthly','月还款金额');
        $grid->column('pay_count','期数');
        $grid->column('out_at','放贷日期');
        $grid->column('dianzihuidan','银行电子回单')->gallery(['zooming' => true]);

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
