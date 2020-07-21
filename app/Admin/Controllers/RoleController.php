<?php

namespace App\Admin\Controllers;

use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.roles');
    }

    protected $status = [1=>'启用','停用'];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $roleModel = config('admin.database.roles_model');

        $grid = new Grid(new $roleModel());

        $grid->column('id', 'ID')->sortable();
//        $grid->column('slug', trans('admin.slug'));
        $grid->column('name', trans('admin.name'));
        $grid->column('desc','角色描述');
        $grid->column('status','状态')->using($this->status);

//        $grid->column('permissions', trans('admin.permission'))->pluck('name')->label('info');

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
        $roleModel = config('admin.database.roles_model');

        $show = new Show($roleModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', trans('admin.slug'));
        $show->field('name', trans('admin.name'));
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
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');
        $userRoleModel = config('admin.database.role_users_model');

        $form = new Form(new $roleModel());

        $form->display('id', 'ID');

        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');

        // 只能赋予自己有的权限,  避免员工新开超管权限
        if(Admin::user()->isRole('administrator')){
            $form->listbox('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));
        }else{
            $userModel = config('admin.database.users_model');
            $user = $userModel::query()->find(Admin::user()->id);
            $role_ids = $user->roles->pluck('id')->toArray();

            $permission_id1 = $user->permissions->pluck('id')->toArray();
            $permission_id2 = DB::table(config('admin.database.role_permissions_table'))->whereIn('role_id',$role_ids)->pluck('permission_id')->toArray();
            $permission_ids = array_merge($permission_id1,$permission_id2);
            dd($permissionModel::query()->whereIn('id',$permission_ids)->pluck('name', 'id'));
            $form->listbox('permissions', trans('admin.permissions'))->options($permissionModel::query()->whereIn('id',$permission_ids)->pluck('name', 'id'));
        }

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $role = Role::query()->find($id);
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body(view('admin.auth.roles.form',['role'=>$role]));
//            ->body($this->form()->edit($id));
    }

    public function updateForView(Request $request)
    {
        $role_id = $request->get('id');
        $permission = $request->get('permission');

        // 每次更新都删除当前角色的全部权限
        DB::table('admin_role_permissions')->where('role_id',$role_id)->delete();

        DB::beginTransaction();
        try{
            foreach ($permission as $p){
                $pModel = Permission::query();
                // 插入前检查是否有这些权限
                if(! $pModel->where('slug',$p)->exists()){
                    $pid = $pModel->insertGetId([
                        'name'=>$p,
                        'slug'=>$p,
                        'http_path'=>'/'.str_replace('.','/',$p)
                    ]);
                }else{
                    $pid=$pModel->where('slug',$p)->first();
                    $pid=$pid->id;
                }
                // 重新插入权限
                DB::table('admin_role_permissions')->insert([
                    'role_id'=>$role_id,
                    'permission_id'=>$pid
                ]);
            }
            $role = Role::query()->find($request->id);
            $role->desc = $request->get('desc');
            $role->name = $request->get('name');
            $role->slug = $request->get('slug');
            $role->status = $request->get('status');
            $role->save();
            DB::commit();
        }catch (\Exception $exception){
            dd($exception);
            return back()->withErrors($exception->getMessage())->withInput();
        }
        return redirect('/admin/roles');
    }


    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form());
    }
}
