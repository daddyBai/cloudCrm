<?php

namespace App\Admin\Controllers;

use App\Models\CrmConfig;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\URL;

class CrmConfigController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Example controller';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CrmConfig());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('type', __('类型'))->sortable()->editable('select',CrmConfig::CONFIG_TYPE);
        $grid->column('key', __('配置项'))->sortable()->editable();
        $grid->column('value', __('对应值'))->sortable()->editable();
        $grid->column('sort', __('排序'))->sortable()->editable();
        $grid->column('status', __('是否启用'))->sortable()->switch(CrmConfig::SWITCH_STATES);
        $grid->column('add_by', __('创建者'))->sortable();
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
        $show = new Show(CrmConfig::findOrFail($id));

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
        $form = new Form(new CrmConfig);

        $form->display('id', __('ID'));
        $form->select('type','配置项类型')->options(CrmConfig::CONFIG_TYPE)->required();
        $form->text('key','配置项')->required();
        $form->text('value','对应值')->required();
        $form->switch('status','是否启用')->states(CrmConfig::SWITCH_STATES);
        $form->number('sort','排序')->default(0);
        $form->display('add_by','创建者员工编号')->default(Admin::user()->id);
        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));

        $form->footer(function ($footer){
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
        });

        return $form;
    }

    public function refreshRedis()
    {
        if(CrmConfig::refreshCache()){
            admin_toastr('清理成功');
        }else{
            admin_toastr('清理失败','error');
        }

        return redirect(URL::previous());
    }
}
