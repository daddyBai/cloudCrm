<?php

namespace App\Admin\Controllers;

use App\Models\CallRecords;
use App\Models\Department;
use App\Models\Mission;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MissionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '外呼任务';

    protected $status = [1=>'已完成',2=>'未完成',3=>'失败'];

    protected $category = [1=>'部门任务',2=>'个人任务'];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Mission());
        $grid->column('category','分类')->using($this->category);
        $grid->column('department_id','所属部门')->using(Department::allDepartment());
        $grid->column('employee_id','所属销售')->using(User::Employees());
        $grid->column('name','任务名称');
        $grid->column('start_at','开始时间')->date('Y-m-d');
        $grid->column('end_at','结束时间')->date('Y-m-d');
        $grid->column('wanted','外呼任务数');

        $grid->column('finished','完成率')->display(function ($model){
            if($model == 0){
                if($this->category == 1){
                    $departs = Department::query()->where('parent_id',$this->department_id)->pluck('id');
                    $departs->add($this->department_id);
                    $users = User::query()->whereIn('department_id',$departs->toArray())->pluck('id');
                }else{
                    $users = $this->employee_id;
                }
                $finished = CallRecords::query()
                    ->whereIn('employee_id',$users)
                    ->whereBetween('call_at',[$this->start_at,$this->end_at])
                    ->count();
                $status = $finished >= $this->wanted ? 1 : 2;
                $endTime = Carbon::parse($this->end_at);
                $nowTime = Carbon::now();
                if($endTime->lte($nowTime) && $status == 2){
                    $status = 3;
                }
                Mission::query()->find($this->id)->update(['finished'=>$finished,'status'=>$status]);
                $model = $finished;
            }
            return $this->wanted > 0 ? round($model / $this->wanted * 100 , 2) : 0;
        })->progressBar($style = 'success', $size = 'sm', $max = 100);
        $grid->column('publish_by','任务发布人')->using(User::Manager());
        $grid->column('status','任务状态')->using($this->status);
        $grid->column('note','备注');

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter){
                $filter->equal('status','任务状态')->select($this->status);
                $filter->like('name','任务名称');
                $filter->between('start_at','任务开始时间')->date();
            });
            $filter->column(1/2,function ($filter){
                $filter->equal('department_id','部门')->select(Department::allDepartment());
                $filter->equal('employee_id','坐席')->select(User::Employees());
                $filter->between('end_at','任务结束时间')->date();
            });
        });

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
        $show = new Show(Mission::findOrFail($id));

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
        $form = new Form(new Mission);

        $form->display('id', __('ID'));
        $form->text('name','任务名称')->required();
        $form->dateRange('start_at','end_at','任务周期')->required();
        $form->number('wanted','外呼任务数')->required();
        $form->radio('category','任务对象')->options($this->category)
            ->when(1,function (Form $form){
                $form->select('department_id','部门')->options(Department::allDepartment());
            })
            ->when(2,function (Form $form){
                $form->select('employee_id','任务坐席')->options(User::Employees());
            })->required();
        $form->hidden('status')->default(2);
        $form->hidden('publish_by','发布人')->default(Admin::user()->id);
        $form->textarea('note','备注');

        return $form;
    }
}
