<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\tabMenu;
use App\Models\CrmConfig;
use App\Models\Department;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DepartmentController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '部门管理';



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Department());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name','部门名称');
        $grid->column('parent_id','上级部门')->using(Department::allDepartment());
        $grid->column('leader','部门主管')->using(User::Manager());
//        $grid->column('target','设定目标');
//        $grid->column('finished','已完成目标');

//        $grid->column('rant','完成率')->display(function ($model){
//            return $this->target > 0 ? round($this->finished / $this->target * 100 , 2) : 0;
//        })->progressBar($style = 'success', $size = 'sm', $max = 100);

        $grid->column('employee','包括员工')->display(function ($model){
            foreach ($model as $k => $emp){
                $model[$k] = User::Employees()[$emp];
            }
            return $model;
        })->label();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

//        $grid->column('target', '总目标')->totalRow();
//        $grid->column('finished', '总达成')->totalRow();


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
        $form->text('name','部门名称');


        if($form->isCreating()){
            $form->select('leader','部门主管')->options(User::Manager());
            $form->multipleSelect('employee','包含员工')->options(User::EmployeesWithoutDepartment());
        }else{
            $form->select('leader','部门主管')->options(User::Manager())->disable();
            $form->multipleSelect('employee','包含员工')->options(User::Employees());
        }
        $form->currency('target','部门目标')->symbol('￥');
        $form->currency('finished','已完成目标')->symbol('￥')->disable();
        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));

        $form->saved(function (Form $form){
           // 同步部门信息到admin_user表  新增时...
            User::joinDepartment($form->model()->id, array_filter($form->employee));
            CrmConfig::delCache(date('Ymd',time()).'-EmployeesWithoutDepartment-');
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
