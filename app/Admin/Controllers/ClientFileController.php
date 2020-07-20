<?php

namespace App\Admin\Controllers;

use App\Admin\Traits\tabMenu;
use App\Models\Client;
use App\Models\ClientFiles;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ClientFileController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客户附件';

    public $file_type = [1=>'文件',2=>'图片'];
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($client_id = 0)
    {
        $grid = new Grid(new ClientFiles());

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
        $grid->column('client_id', '客户ID');
        $grid->column('type', '文件类型')->using($this->file_type);
        $grid->column('name', '文件名');
        $grid->column('filepath', '文件地址')->downloadable();
//        $grid->column('size', '大小');
        $grid->column('employee_id', '员工')->using(User::Employees());
        $grid->column('created_at','上传时间');
//        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(ClientFiles::findOrFail($id));

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
        $form = new Form(new ClientFiles);

        $form->hidden('client_id','客户ID')->default($client_id);
        $form->hidden('employee_id','员工ID')->default(Admin::user()->id);
        $form->radio('type','文件类型')->options($this->file_type)
            ->when(1,function (Form $form){
                $form->text('name','文件说明')->help('例：中国银行流水');
                $form->file('filepath','文件')->uniqueName();
            })
            ->when(2,function (Form $form){
                $form->text('name','图片说明')->help('例：营业执照照片');
                $form->image('filepath','图片')->uniqueName();
            });

        $form->display('id', __('ID'));
        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));

        $form->saving(function (Form $form){

        });

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
