<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\BatchDistribution;
use App\Exports\ClientExport;
use App\Imports\SeaImport;
use App\Models\China;
use App\Models\Client;
use App\Models\CrmConfig;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Media\MediaManager;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SeaController extends BaseController
{
    /**
     * Title for born resource.
     *
     * @var string
     */
    protected $title = '客户公海';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Client());
        $grid->disableCreateButton();
        $grid->exporter(new ClientExport());

        // 筛选没有人负责的客户为公海
        $grid->model()->where('in_sea',1);

        $grid = $this->permissions($grid, true,true,false,false);
        $grid->column('id', __('ID'))->sortable();
        $grid->column('name','姓名')->display(function ($model){
            return "<a href='/admin/sea/$this->id/edit'>$model</a>";
        });
        $grid->column('mobile','手机号(点击拨号)');
        $grid->column('employee_status','跟进状态')->using(CrmConfig::getKeyValue('follow_status'));
        $grid->column('created_at','创建时间');

        $grid->filter(function ($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->column(1/2,function ($filter){
                $filter->like('name','客户姓名');
                $filter->in('employee_status','跟进状态')->multipleSelect(CrmConfig::getKeyValue('follow_status'));
            });
            $filter->column(1/2,function ($filter){
                $filter->equal('mobile','手机号码');
                $filter->equal('employee_id','员工')->select(User::Employees());
            });
        });

        $grid->batchActions(function ($batch){
            $batch->add(new BatchDistribution());
            $batch->disableDelete();
        });

        // 每页条数
        $grid->perPages([10,20,50,100,200,500,1000]);


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
        $show = new Show(Client::findOrFail($id));

        $show->field('name','姓名');
        $show->field('sex','性别')->using([1=>'男',2=>'女']);
        $show->field('mobile','手机号')->filter();
        $show->field('status','客户状态')->using(CrmConfig::getKeyValue('client_status'));
        $show->field('marriage','婚姻状况')->using(Client::marriage);
        $show->field('email','邮箱');
        $show->field('education','学历')->using(Client::education);
        $show->field('age','年龄');

        $show->field('china_id','身份证号')->as(function ($model){
            return '不可查看';
        });
        $show->field('china_id_valid','身份证有效期');
        $show->field('current_prov','当前地址')->as(function ($model){
            return China::change($model)
                .' - '.China::change($this->current_city)
                .' - '.China::change($this->current_area)
                .' - '.$this->current_street;
        });
        $show->field('born_prov','籍贯地址')->as(function ($model){
            return China::change($model)
                .' - '.China::change($this->born_city)
                .' - '.China::change($this->born_area)
                .' - '.$this->born_street;
        });
        $show->field('income','本人月收入(¥)');
        $show->field('family_in','家庭月收入(¥)');
        $show->field('family_out','家庭月支出(¥)');
        $show->field('shebao','社保')->using(CrmConfig::SWITCH_STATES_YM);
        $show->field('shebao_in','个人月缴存额（¥）：');
        $show->field('gongjijin','公积金（¥）：')->using(CrmConfig::SWITCH_STATES_YM);
        $show->field('gongjijin_in','个人月缴存额（¥）：');
        $show->field('company','单位名称');
        $show->field('company_address','单位地址');
        $show->field('company_type','行业类型');
        $show->field('company_belong','单位性质')->using(Client::company_type);
        $show->field('company_post','公司职位')->using(Client::company_post);
        $show->field('debt','剩余负债');
        $show->field('monthly_debt','月还款额度');
        $show->field('note','备注');
        $show->field('employee_id','所属销售')->using(User::Employees());
        $show->field('employee_status','跟进状态')->using(CrmConfig::getKeyValue('follow_status'));
        $show->field('house','房产数量');
        $show->field('car','车产数量');
        $show->field('updated_at','更新时间');
        $show->field('created_at','创建时间');



        return  $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
    }

    public function import(Request $request)
    {
        $files = $request->file('files');
        $dir = $request->get('dir', '/');
        $manager = new MediaManager($dir,'xlsx');
        try {
            //文件上传服务器
            if ($manager->upload($files)) {
                admin_toastr('导入成功');
            }
            //文件存储路径
            $filePath = storage_path()."/app/public/".$files[0]->getClientOriginalName();
            Excel::import(new SeaImport(),$filePath);
        } catch (\Exception $e) {
            admin_toastr("文件导入失败".$e->getMessage(), 'error');
        }
        return back();
    }

    public function getAllClients()
    {
        return Client::getAllClients();
    }

    public function test()
    {


        foreach (range(1,500) as $id){
            $user = $this->randomUser();
            try{
                DB::table('crm_client')->insert($user);
            }catch (\Exception $exception){
                dd($user,$exception);
            }

        }
    }

    public function randomUser()
    {
        $user = [];
        $xing = ['王','李','张','刘','陈','杨','黄','赵','周','吴','徐','孙','马','胡','朱','郭','何','罗','高','林'];
        $ming = ['剑祎','煜堇','泳鸿','国轩','豪彬','明杰','琪智','曦艺','辰肃','健柱','轩熠','彰图','喜曦','励宇','墨皓','烨言','禹霖','瀚天','永羲','俊泽','胤诩','诚涛','礼烨','晨宸','飞俊','枫哲','实羽','源图',
            '逸喜','圣腾','镜冰','向逸','煜康','昊凯','赡康','依伦','堇煊','晨诗','天豪','尚新','盛乾','翰绅','骏渊','忆影','东强','宇乐','鸿骏','哲卓','释江','景术','圣善','川泰','煊萧','琦诰','浩宣','顺豪',
            '项凯','韬棚','冰浩','天骏','锵官','旭尚','冰棚','豪尊','博宁','萧俊','宇天','笃弘','天浩','林诚','岁宣','雄庆','川昆','邈架','墨豪','烨烁','伟羽','骏玄','泽征','俊豪','春镇','宣烨','鸣诚','朗厚'];
        $ming2 = ['淑榆','菡蕊','语枫','佩珊','梦嫣','彤渟','晴波','静曼','明莎','初曼','思菱','寄瑶','蔓叶','亦玉',
            '元瑶','又菱','梦芳','惜珊','博觅','夏雪','青梦','月涵','傲丝','绮山','紫雪','铃夏','凝芙','瑞南','韶天','雪巧','惜文','静晴','宛秋','珑若','迎南','彤鱼'];

        $user['sex'] = random_int(1,2);
        $user['marriage'] = random_int(1,2);

        $user['education'] = random_int(1,6);
        $user['age'] = random_int(18,55);
        $user['income'] = random_int(3,50) * 500;
        $user['family_in'] = $user['income']*random_int(90,500)/100-random_int(3,15)*100;
        $user['family_out'] = $user['family_in']*random_int(20,200)/100+random_int(3,20)*100;

        $user['shebao'] = random_int(1,2);
        $user['shebao_in'] = $user['shebao'] == 1 ? $user['income'] * 0.07 : '';

        $user['gongjijin'] = random_int(1,2);
        $user['gongjijin_in'] = $user['gongjijin'] == 1 ? $user['income'] * 0.03 : '';

        $user['debt'] = random_int(1,100) > 85 ? random_int(1,100)*10000 : 0;
        $user['monthly_debt'] = $user['debt'] > 0 ? $user['debt'] * 1.78 / 360 : 0;

        $user['employee_id'] = random_int(1,20) > 12 ? random_int(3,6) : '';
        $user['employee_status'] =  $user['employee_id'] > 1 ? random_int(1,6) : '';

        $user['house'] =  $user['debt'] > 300000 ? random_int(1,3) : 0;
        $user['car'] = $user['debt'] > 100000 ? random_int(1,2) : 0;

        $user['name'] = $user['sex']==1 ? $xing[array_rand($xing)].$ming[array_rand($ming)] :  $xing[array_rand($xing)].$ming2[array_rand($ming2)];
        $user['mobile'] = "1".random_int(3,9).random_int(10000,99999).random_int(1000,9999);
        $user['status'] = array_rand(range('A','E'));

        $user['created_at'] = Carbon::parse(time())->subSecond(random_int(6000,10000000))->toDateTimeString();
        $user['updated_at'] = random_int(1,5) > 3 ? Carbon::parse($user['created_at'])->subSecond(random_int(600,6000))->toDateTimeString():'';

        $dz = random_int(1,100);

        if($dz>60){
            $user['current_prov'] = 510000;
            $city = random_int(1,100) > 92 ? random_int(3,20)*100 : 100;
            if(strlen($city) == 4){
                $city = substr($city, 0, -1);
            }
            $user['current_city'] = "510".sprintf("%03d",$city);
            $user['current_area'] =  substr($user['current_city'], 0, -1) . random_int(1,9);

            $user['current_street'] = $this->randRoad();
        }
        if ($dz > 70){
            $user['born_prov'] = 510000;
            $city = random_int(1,100) > 40 ? random_int(3,20)*100 : 100;
            if(strlen($city) == 4){
                $city = substr($city, 0, -1);
            }
            $user['born_city'] = "510".sprintf("%03d",$city);
            $user['born_area'] = substr($user['born_city'], 0, -1) . random_int(1,9);
            $user['born_street'] = $this->randRoad();
        }

        if(isset($user['born_area']) && ! empty($user['born_area'])){
            $user['china_id'] = $user['born_area'] .
                (2020-$user['age']).
                sprintf("%02d", random_int(1,12)).
                sprintf("%02d", random_int(1,31)).
                sprintf("%04d", random_int(1,9999));
            $vdd = (2020+random_int(1,5)).'-'.sprintf("%02d", random_int(1,12)).'-'.sprintf("%02d", random_int(1,31));
            $user['china_id_valid'] = Carbon::parse($vdd)->toDateString();
        }

        $user['company'] = random_int(1,100) > 70 ? $this->randRoad(true) : '';
        if($user['company']){
            $user['company_address'] = $this->randRoad();
            $c99 = ['保险业','采矿','能源','餐饮','宾馆','电讯业','房地产','服务','服装业','公益组织','广告业','航空航天','化学','健康','保健','建筑业','教育','培训','计算机','金属冶炼','警察','消防','军人','会计','美容','媒体','出版','木材','造纸','零售','批发','农业','旅游业','司法','律师','司机','体育运动','学术研究','演艺','医疗服务','艺术','设计','银行','金融','因特网','音乐舞蹈','邮政快递','运输业','政府机关','机械制造','咨询'];
            $user['company_type'] = $c99[array_rand($c99)];
            $user['company_belong'] =array_rand(Client::company_type);
            $user['company_post'] = array_rand(Client::company_post);
        }

        foreach ($user as $key => $item){
            if(empty($item)){
                unset($user[$key]);
            }
        }
        return $user;
    }

    public function randRoad($companyName = false)
    {
        $road1 = ['南京','太原','上海','福建','黑龙江','云南','湖南','湖北,山西','陕西','北京','广西','广东','广州','南宁'];
        $road2 = ['一','二','三','四','五','六','七'];
        $road3 = ['上','下','左','右','东','南','西','北','前','后','城','大','小'];
        $road4 = ['路','街','巷'];
        $road5 = ['浣花溪','浆洗街','桐梓林','春熙路','百草路'];
        $company = ['霆','晓','衡','儒','静','常','浩','茗','杰','智','翰','蔚','忆','双','涛','丽','韵','耀','艺','巍','兰','雪','尧',
            '贝','仑','青','笑','宗','雨','虹','纪','亭','俊','禹','垚','秋','倩','宸','甜','加','茜','涵','琳','微','菡','萱',
            '金','新','中','盛','亚','信','华','豪','奥','凯','泰','鑫','创','宝','星','联','晨','百','尔','海','瑞','科','锦',
            '成','翔','隆','迪','赛','睿','艾','高','德','雅','格','纳','欣','亿','维','锐','菲','佳','沃','策','优','晟','捷',
            '乐','飞','福','皇','嘉','达','佰','美','元','亮','名','欧','特','辰','康','讯','鹏','腾','宏','伟','钧','思','正',
            '旺','融','誉','东','森','际','巨','骄','为','诚','妙','英','虹','芬','馨','尼','迈','群','拓','建','江','雷','天',
            '博','扬','索','蓝','昂','兴','聚','鸿','略','众','汇','圣','卓','宇','国','普','绿','斯','登','诺','恒','辉','缘',
            '聪','垒','蕾','瀚','骁','永','吉','先','君','依','昌','哲','营','舒','曙','廷','渲','梦','瑜','菏','凤','叶','卫',
            '臻','燕','霖','霏','莲','灿','颜','麒','韬','露','鹤','骄','厅','湾','凡','可','巧','弘','谊','影','慧','洁','润',
            '致','春','帅','禾','竹','多','帆','秀','盈','泓','品','庭','展','朔','轩','育','航','津','启','振','聆','翌','迎',
            '婷','越','岚','超','清','云','淼','业','义','意','资','湘','会','菁','萌','语','荣','赫','宁','铭','齐','毅','进',
            '易','威','玛','日','伦','道','发','唯','一','才','月','丹','文','立','玉','平','同','志','宜','林','奇','政','朋',
            '诗','香','鼎','碧','麦','邦','克','凡','利','卡','多','安','尚','川','州','帝','悦','逸','风','丰','壹','泽','旭',
            '情','明','滋','祥','彩','朗','郎','爱','景','帆','阳','驰','通','骏','力','顺','领','迅','途','益','和','园','波'];
        $company2 = ['保险业','采矿','能源','餐饮','宾馆','电讯业','房地产','服务','服装业','公益组织','广告业','航空航天','化学','健康','保健','建筑业','教育','培训','计算机','金属冶炼','警察','消防','军人','会计','美容','媒体','出版','木材','造纸','零售','批发','农业','旅游业','司法','律师','司机','体育运动','学术研究','演艺','医疗服务','艺术','设计','银行','金融','因特网','音乐舞蹈','邮政快递','运输业','政府机关','机械制造','咨询'];
        $company3 = ['公司','集团','有限责任公司','','',''];

        if($companyName){
            $name = $company[array_rand($company)].$company[array_rand($company)];
            $name .= random_int(1,10) > 4 ?  $company[array_rand($company)]:'';
            $name .= random_int(1,10) > 4 ?  $company[array_rand($company)]:'';
            $name .= random_int(1,10) > 4 ?  $company2[array_rand($company2)]:'';
            $name .= random_int(1,10) > 4 ?  $company3[array_rand($company3)]:'';
            return $name;
        }

        switch (random_int(1,5)){
            case 1:
                $road = $road1[array_rand($road1)] . random_int(1,10) > 5 ? $road2[array_rand($road2)] : $road3[array_rand($road3)];
                break;
            case 2:
                return $road5[array_rand($road5)] ;
                break;
            case 3:
                $road = $company[array_rand($company)].$company[array_rand($company)] . random_int(1,10) > 5 ? $road3[array_rand($road3)] : '';
                break;
            case 4:
                $road = $company[array_rand($company)] . random_int(1,10) > 5 ? $road3[array_rand($road3)] :  $road2[array_rand($road2)];
                break;
            case 5:
                $road = $road3[array_rand($road3)] . random_int(1,10) > 5 ? $road2[array_rand($road2)] :  $company[array_rand($company)];
                break;
            default:
                $road = $road1[array_rand($road1)] . random_int(1,10) > 5 ? $road3[array_rand($road3)] : '';
                break;
        }

        return $road . $road4[array_rand($road4)] ;
    }
}
