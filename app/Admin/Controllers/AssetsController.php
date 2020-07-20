<?php

namespace App\Admin\Controllers;

use App\Models\Assets;
use App\Models\CrmConfig;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AssetsController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Example controller';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Assets());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Assets::findOrFail($id));

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
        $form = new Form(new Assets());

        $form->select("client_id","客户姓名")->options('/admin/ajax/getAllClients');
        $form->select('category','资产类别')
            ->options(['house'=>'房产','car'=>'车产'])
            ->when('house', function (Form $form){
                $form->select('pay_type','购房类型')->options(CrmConfig::getKeyValue('money_pay_type'));
                $form->select('usefor','购房用途')->options(CrmConfig::getKeyValue('house_use_for'));
                $form->switch('status','是否抵押')->states(CrmConfig::SWITCH_STATES_YN);
                $form->number('house_size','房产面积');
                $form->number('value','房产市值(万)');
            })
            ->when('car',function (Form $form){
                $form->number('value','车辆价格(万)');
                $form->date('buy_time','购入日期');
                $form->switch('status','是否抵押')->states(CrmConfig::SWITCH_STATES_YN);
            })->required();

        return $form;
    }
}
