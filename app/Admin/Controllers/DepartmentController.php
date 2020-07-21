<?php

namespace App\Admin\Controllers;

use App\Models\CrmConfig;
use App\Models\Department;
use App\Models\User;
use Encore\Admin\Form;
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

        return $content->header('部门管理')
            ->row(function ($row) {
                $row->column(10, $this->treeView()->render());
            });
    }


    protected function treeView()
    {
        $tree = new Tree(new Department());

        $tree->query(function ($model){
            return $model->with('employees');
        });

        $tree->branch(function ($branch) {
            $count = empty($branch['employees']) ? 0 : count($branch['employees']);
            $leader = empty($branch['leader']) ? '' : User::Users()[$branch['leader']];
            $employees = empty($branch['employees']) ? 0 : join(', ',collect($branch['employees'])->pluck('name')->toArray());
            $payload = "&nbsp;<strong>{$branch['title']}  &nbsp;&nbsp;( {$count} 人)  </strong>";
            $payload .= "<small style='color: grey'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;主管： $leader</small>";
            $payload .= "<small style='color: grey'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;组员： $employees</small>";
            return $payload;
        });
        return $tree;
    }

    public function create(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form());
    }

    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form()->edit($id));
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

    protected function form()
    {
        $form = new Form(new Department());
        $deps = Department::allDepartment();
        $deps[0] = '根目录';
        ksort($deps);

        $form->hidden('id', __('ID'));
        $form->text('title','部门名称')->required();
        $form->select('parent_id','上级部门')->options($deps);
        $form->select('leader','部门领导')->options(User::Users())->required();
        $form->multipleSelect('employee','部门成员')->options(User::UserWithoutDepartment());

        $form->saved(function (Form $form){
            // 同步部门信息到admin_user表  新增时...

            User::query()->where('department_id',$form->id)->update(['department_id'=>NULL,'is_leader'=>2]);
            User::joinDepartment($form->model()->id, array_filter($form->employee));
            User::joinDepartment($form->model()->id, [$form->leader]);

            $dep = Department::query()->whereNotNull('leader')->pluck('leader');
            User::query()->update(['is_leader'=>2]);
            User::query()->whereIn('id',$dep)->update(['is_leader'=>1]);

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


        return $form;
    }


}
