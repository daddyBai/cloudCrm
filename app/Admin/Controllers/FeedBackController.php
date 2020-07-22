<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\tabMenu;
use App\Models\CallBack;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class FeedBackController extends BaseController
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
    protected function grid($client_id = 0)
    {
        $grid = new Grid(new CallBack());

        if($client_id > 0){
            $grid->model()->where('client_id',$client_id);
        }



        $grid->disableActions();
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->column('id', __('ID'))->sortable();
        $grid->column('client_id', '客户姓名')->using(Client::getAllClients());

            $grid->column('employee_id', '所属员工')->using(User::Employees());

        $grid->column('client_feel','客户满意度')->using(CrmConfig::getKeyValue('client_feeling'));
        $grid->column('client_said','客户反馈');
        $grid->column('callback_by','回访形式')->using($this->callback_by);
        $grid->column('callback_at','回访时间');
        $grid->column('filepath','反馈证明');

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
        $show = new Show(CallBack::findOrFail($id));

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
        $form = new Form(new CallBack);

        $client_name = $client_id > 0 ? Client::getAllClients()[$client_id] : '';

        $form->hidden('client_id','客户id')->value($client_id);
        $form->hidden('employee_id','')->value(self::current_id());
        $form->text('client_name','客户姓名')->value($client_name)->readonly();
        $form->datetime('callback_at','回访日期');
        $form->select('callback_by','回访形式')->options($this->callback_by);
        $form->select('client_feel','客户满意度')->options(CrmConfig::getKeyValue('client_feeling'))->value(1);
        $form->textarea('client_said','客户反馈');
        $form->image('filepath','反馈证明');

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
