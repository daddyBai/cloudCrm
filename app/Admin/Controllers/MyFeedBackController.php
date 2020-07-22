<?php

namespace App\Admin\Controllers;

use App\Models\CallBack;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\MoneyOut;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MyFeedBackController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户回访';

    protected $callback_by = [1=>'电话',2=>'微信'];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CallBack());

        $grid->model()->where('employee_id',self::current_id());
        $grid->disableActions();
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->column('id', __('ID'))->sortable();
        $grid->column('client_id', '客户姓名')->using(Client::getAllClients());
        $grid->column('employee_id', '所属员工')->using(User::Employees());
        $grid->column('client_feel','客户满意度')->display(function ($model){
            $star = '';
            for ($i =0;$i<$model;$i++){
                $star.="♥";
            }
            return $star;
        });
        $grid->column('client_said','客户反馈');
        $grid->column('callback_by','回访方式')->using($this->callback_by);
        $grid->column('callback_at','回访时间');
        $grid->column('filepath','反馈证明');

        $grid->filter(function (Grid\Filter $filter){
            $filter->column(1/2,function ($filter){

            });

            $filter->column(1/2,function ($filter){
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
