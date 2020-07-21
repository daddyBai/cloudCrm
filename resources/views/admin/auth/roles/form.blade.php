
<style>
    .custom-checkbox{
        float: left;margin: 0 15px;
    }
    .custom-control-input{
        margin: 4px 4px 0 !important;
    }
    td{
        border: 1px solid rgb(57, 57, 57) !important;
    }
    th{
        border: 1px solid rgb(57, 57, 57) !important;color: #ffffff;font-weight: bold;text-align: center;background-color: grey;
    }
    .cbsg{
        background-color: white;
        height: 34px;width: 100%;
        margin-left: 0;
        border: 1px solid #d2d6de;
        line-height: 36px;
    }
</style>
<div class="container">
    <form action="/admin/roles/update/{{$role->id}}" method="post">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="id" value="{{ $role->id }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="name">角色名字</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="请输入角色名字" value="{{ old('name', $role->name) }}">
                </div>
                <div class="form-group">
                    <label for="desc">角色描述</label>
                    <input type="text" class="form-control" name="desc" id="desc" placeholder="请输入角色描述" value="{{ old('desc', $role->desc) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="slug">角色标记</label>
                    <input type="text" class="form-control" name="slug" id="slug" placeholder="请输入角色标记" value="{{ old('slug', $role->slug) }}">
                </div>
                <label for="desc">账号状态</label>
                <div class="row cbsg">
                    <div class="col-md-4"><div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="customRadioInline1" name="status" class="custom-control-input" {{ $role->status == 1 ? 'checked="checked"' :'' }} value="1">
                            <label class="custom-control-label" for="customRadioInline1">启用</label>
                        </div></div>
                    <div class="col-md-4"> <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="customRadioInline2" name="status" class="custom-control-input" {{ $role->status == 2 ? 'checked="checked"' :'' }}  value="2">
                            <label class="custom-control-label" for="customRadioInline2">停用</label>
                        </div></div>
                </div>
            </div>
        </div>

        <div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input' id='selectAll'>
            <label class='custom-control-label' for='selectAll'>全选</label></div>
        <table class="table table-bordered table-dark">
            <thead>
            <tr>
                <th colspan="2" width="35%">菜单权限</th>
                <th width="65%">操作权限</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $permission = [
                'query'=>'查询',
                'create'=>'新增',
                'import'=>'导入',
                'export'=>'导出',
                'forward'=>'转交',
                'edit'=>'编辑',
                'delete'=>'删除',
            ];
            $menu = \DB::table('admin_menu')
                ->get();
            $plist = \DB::table('admin_role_permissions')->where('role_id',$role->id)->pluck('permission_id');
            $slugs = \DB::table('admin_permissions')->whereIn('id',$plist)->pluck('slug')->toArray();
            $current_id = 0;
            foreach ($menu as $key => $m){
                $checked = in_array($m->permission,$slugs) ? "checked='checked'" : '';
                $checkbox =  "<div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input ckbox menu-checkbox' $checked id='$m->permission' name='permission[]' value='$m->permission'>
                                    <label class='custom-control-label' for='$m->permission'>$m->title</label></div>";
                if($m->parent_id == 0){
                    $current_id=$m->id;
                    echo "<tr><td>$checkbox</td><td></td><td></td></tr>";
                }else{
                    if($m->parent_id == $current_id){
                        // 权限里面没有 . 的，才给加按钮操作
                        $btnCheckbox = '';
                        if(! strstr($m->permission, '.')){
                            foreach ($permission as $v => $k){
                                $btnCheckbox .=  "<div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input ckbox btn-checkbox' $checked id='$m->permission$v' name='permission[]' value='$m->permission.$v'>
                                    <label class='custom-control-label' for='$m->permission$v'>$k</label></div>";
                            }
                        }
                        echo "<tr><td></td><td>$checkbox</td><td>$btnCheckbox</td></tr>";
                    }
                }
            }
            ?>

            </tbody>
        </table>
        <button class="btn btn-primary " type="submit"> 确定 </button>

    </form>
</div>

<script type="text/javascript">
    $("#selectAll").click(function () {
        if ($(this).prop("checked")) {
            $('.ckbox').attr('checked',true)
        } else {
            $('.ckbox').removeAttr('checked')
        }

    });
</script>
