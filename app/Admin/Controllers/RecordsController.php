<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\tabMenu;
use App\Models\Client;
use App\Models\ClientRecords;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class RecordsController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '跟进记录';

    public function handle(Request $request)
    {

        return $this->next();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($client_id=0)
    {
        $grid = new Grid(new ClientRecords());

        if($client_id>0){
            $grid->model()->where('client_id',$client_id);
        }
        $grid->actions(function (Grid\Displayers\Actions $actions){
           $actions->disableView();
           $actions->disableEdit();
        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();

        $grid->column('client_id','客户姓名')->using(Client::getAllClients());
        $grid->column('employee_id','销售')->using(User::Employees());
        $grid->column('records','跟进记录');
        $grid->column('plan','跟进计划');
        $grid->column('record_at','计划时间');
        $grid->column('created_at', '创建时间');

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

    public function edit($client_id, Content $content)
    {
        $client_name = Client::getAllClients()[$client_id];
        return $content
            ->title($this->title().' - '.$client_name)
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->row(function ($row) use ($client_id){
                $row->column(12, tabMenu::tabMenuList($client_id));
            })
            ->row(function ($row) use ($client_id){
                $row->column(6, $this->grid($client_id));
                $row->column(6, $this->form($client_id));
            });
    }
    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ClientRecords::findOrFail($id));

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
    protected function form($client_id = 0)
    {
        $form = new Form(new ClientRecords);

        $form->display('id', __('ID'));
        $form->hidden('client_id','客户ID')->default($client_id);
        $form->hidden('employee_id','员工ID')->default(self::current_id());
        $form->textarea('records','跟进记录')->required();
        $form->textarea('plan','操作计划');
        $form->datetime('record_at','计划时间');
        $form->hidden('client.last_updated_at','上次联系时间')->value(Carbon::now()->toDateTimeString());
        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));

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
