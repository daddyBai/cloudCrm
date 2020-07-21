<?php
namespace App\Admin\Controllers;

use App\Models\CallRecords;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Admin\Traits\tabMenu;

class DialController extends BaseController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '外呼记录';
    protected $client=[];
    protected $client_id=0;
    protected $call_type = [1=>'普通来电',2=>'外呼去电'];


    public function show($id, Content $content)
    {
        $client_name = Client::getAllClients()[$id];
        $content
            ->title($this->title().' - '.$client_name)
            ->description($this->description['show'] ?? trans('admin.show'))
            ->row(function ($row) use ($id){
                $row->column(12, tabMenu::tabMenuList($id));
            })
            ->body($this->grid($id));
        return $content;
    }

    public function dial($id,Content $content)
    {
        $client_id = $id;
        $this->client = $client = Client::query()->where('id',$id)->first()->toArray();
        $callRecords = new CallRecords();
        // todo 在这里调用第三方拨号 api
        $rcd = [
            'client_id'=>$client['id'],
            'employee_id'=>Admin::user()->id,
            'call_type'=>2,
            'call_from'=>'',
            'call_to'=>$client['mobile'],
            'client_name'=>Client::getAllClients()[$client['id']],
            'call_at'=>Carbon::parse(time())->toDateTimeString(),
        ];
        $record_id = $callRecords->insertGetId($rcd);
        return $this->edit($record_id,$content,$client_id);
    }


    public function edit($id, Content $content,$client_id=0)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($client_id)->edit($id));
    }


    /**
     * Make a grid builder.
     * @param $id
     * @return Grid
     */
    public function grid($id = 0)
    {
        $grid = new Grid(new CallRecords());
        if($id > 0){
            $grid->model()->where('client_id',$id);
        }
        $grid->disableActions();
        $grid->disableRowSelector();
        $grid->disableCreateButton();

        $grid->column('id', __('ID'))->sortable();
        $grid->column('call_type','呼叫类型')->using($this->call_type);
        $grid->column('client_name','客户姓名')->display(function ($model){
            return "<a href='/admin/clue/$this->id/edit'>$model</a>";
        });
        $grid->column('call_from','主叫号码');
        $grid->column('call_to','被叫号码');
        $grid->column('call_to','被叫号码');
        $grid->column('employee_id','通话坐席')->display(function ($model){
            return User::myTitle($model);
        })->style('text-align:center');
        $grid->column('call_duration','通话时长');
        $grid->column('call_status','接听状态')->using(CrmConfig::getKeyValue('call_status'));
        $grid->column('call_file_path','录音地址');

        $grid->filter(function (Grid\Filter $filter){
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->in('call_type', '呼叫类型')->multipleSelect($this->call_type);
                $filter->in('call_status', '接听状态')->multipleSelect(CrmConfig::getKeyValue('call_status'));
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('client_name', '客户姓名');
                $filter->between('created_at', '拨号时间')->datetime();
                if (self::isEmployee()) {

                } else {
                    $filter->equal('employee_id', '员工')->select(User::Employees());
                }
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
        $show = new Show(CallRecords::findOrFail($id));

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
        $form = new Form(new CallRecords());
        $time_now = Carbon::parse(time())->toDateTimeString();

        $form->display('id', __('ID'));
        $form->hidden('client_id','客户id');
        $form->text('client_name','客户姓名')->disable();
        $form->hidden('employee_id','销售姓名')->disable();
        $form->hidden('call_type','呼叫类型')->disable();
        $form->text('call_from','主叫号码')->disable();
        $form->text('call_to','被叫号码')->disable();
        $form->text('call_at','拨号时间')->disable();
        $form->text('call_duration','通话时长')->disable();
        $form->select('call_status','接通状态')->options(CrmConfig::getKeyValue('call_status'))->required();
        $form->text('call_file_path','通话录音地址')->disable();
        $form->hidden('created_at', __('Created At'))->default($time_now);
        $form->hidden('updated_at', __('Updated At'))->default($time_now);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->saved(function (Form $form){
            return redirect( tabMenu::menuList($form->model()->client_id,"活动"));
        });

        return $form;
    }



}
