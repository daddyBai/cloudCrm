<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\tabMenu;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\MoneyOut;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MoneyOutController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '贷款管理';

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
        $grid->column('dianzihuidan','银行电子回单');

        return $grid;
    }

    public function show($client_id, Content $content)
    {
        $client_name = Client::getAllClients()[$client_id];
        $content
            ->title($this->title().' - '.$client_name)
            ->description($this->description['show'] ?? trans('admin.show'))
            ->row(function ($row) use ($client_id){
                $row->column(12, tabMenu::tabMenuList($client_id));
            })
            ->row(function ($row) use ($client_id){
                $row->column(6, $this->grid($client_id));
                $row->column(6, $this->form($client_id));
            });
        return $content;
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
    protected function form($client_id=0)
    {
        $form = new Form(new MoneyOut);



        $form->hidden('client_id', '客户id')->default($client_id);
        $form->hidden('employee_id', '所属员工')->default(Admin::user()->id);

        $client_name = $client_id > 0 ? Client::getAllClients()[$client_id] : '';

        $form->text('client_name', '客户姓名')->default($client_name)->disable();
        $form->textarea('out_id','放贷产品')->required();
        $form->currency('out_money','放贷金额')->symbol("￥");
        $form->currency('fee','居间服务费')->symbol("￥");
        $form->select('pay_type','还款方式')->options(CrmConfig::getKeyValue('money_back_type'));
        $form->currency('pay_monthly','月还款金额')->symbol('￥');
        $form->number('pay_count','期数');
        $form->date('out_at','放贷日期');
        $form->file('dianzihuidan','银行电子回单')->required();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->saved(function (Form $form) use ($client_id){
            return back();
        });

        return $form;
    }
}
