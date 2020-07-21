<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.administrator');
    }

    protected $status = [1=>'启用','停用'];
    protected $isBoss = [1=>'是','否'];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new User());
        $grid->model()->where('id','>',1)->with('department');

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', trans('admin.username'));
        $grid->column('mobile', trans('admin.mobile'));
        $grid->column('name', trans('admin.name'));

        $grid->column('department_id','所属部门')->display(function ($model){
            $currentDep = $model > 0 ? $model : 0;
            $parentDep='';
            if($currentDep > 0){
                $parent = $this->department['parent_id'];
                $parentDep = $parent > 0 ? Department::allDepartment()[$parent].' > ' : '';
                $currentDep = Department::allDepartment()[$currentDep];
            }else{
                $currentDep = '';
            }
            return $parentDep.$currentDep;

        });
        $grid->column('is_leader','是否主管')->using([1=>'是']);
        $grid->column('department_boss','直属上级')->display(function ($model){
            $leader = $this->department['leader'];
            return $leader > 0 ? User::Users()[$leader] : '';
        });
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();
        $grid->column('status','状态')->using($this->status);
        $grid->column('created_at', trans('admin.created_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');

        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('username', trans('admin.username'));
        $show->field('name', trans('admin.name'));
        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->text('mobile', trans('admin.mobile'));
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->select('department_id',trans('admin.department_id'))->options(Department::allDepartment())->required();
        $form->select('status',trans('admin.status'))->options($this->status)->required();

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }
        });

        return $form;
    }
}
