<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\tabMenu;
use App\Models\Client;
use App\Models\Department;
use App\Models\NeedHelp;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class NeedHelpController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '协助人员';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($client_id = 0)
    {
        $grid = new Grid(new NeedHelp());

        if($client_id > 0){
            $grid->model()->where('client_id',$client_id);
        }
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
            $actions->disableEdit();
        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();

        $grid->column('id', __('ID'))->sortable();
        $grid->column('client_id','客户姓名')->using(Client::getAllClients());
        $grid->column('employee_id','销售人员')->using(User::Employees());
        $grid->column('helper_id','协助人员')->using(User::Employees());
        $grid->column('dp','所属部门');
        $grid->column('help_at', '协助时间');

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
        $show = new Show(NeedHelp::findOrFail($id));

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
        $form = new Form(new NeedHelp);

        $form->hidden('client_id','客户姓名')->default($client_id);
        $form->hidden('employee_id','销售人员')->default(Admin::user()->id);
        $form->select('dp','所属部门')->options(Department::allDepartment());
        $form->select('helper_id','协助人员')->options(User::Employees());
        $form->datetime('help_at', '协助时间');

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
