<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\tabMenu;
use App\Models\CrmConfig;
use App\Models\Department;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Tree;

class DepartmentController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '部门管理';


    public function index(Content $content)
    {

        $employees = new Department();
        $employees->query()->withCount('employees');

        $tree = new Tree($employees);

        return $content->header('部门管理')
            ->row(function ($row) use ($tree, $content){
                $row->column(6,$tree);
                $row->column(6,$this->form());
            });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Department());
        $grid->model()->with('employees');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('title','部门名称');
        $grid->column('parent_id','上级部门')->using(Department::allDepartment());
        $grid->column('leader','部门主管')->using(User::Manager());


        $grid->column('employees','包含员工')->display(function ($model){
            dd($model);
        });
        $grid->column('employee','包括员工')->display(function ($model){
            foreach ($model as $k => $emp){
                $model[$k] = User::Employees()[$emp];
            }
            return $model;
        })->label();
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
        $show = new Show(Department::findOrFail($id));

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
        $form = new Form(new Department());

        $form->display('id', __('ID'));
        $form->text('title','部门名称');
        $form->select('parent_id','上级部门')->options(Department::allDepartment());
        $form->select('leader','部门领导')->options(User::Users());
        $form->multipleSelect('employee','部门成员')->options(User::Users());
        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));

        $form->saved(function (Form $form){
            // 同步部门信息到admin_user表  新增时...
            User::joinDepartment($form->model()->id, array_filter($form->employee));
            CrmConfig::delCache(date('Ymd',time()).'-EmployeesWithoutDepartment-');
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->saved(function (Form $form){
            return back();
        });

        return $form;
    }


    public function test()
    {
        $h = new HomeController();
        $rs = $h->MineStat();
        dd($rs);
    }

}
