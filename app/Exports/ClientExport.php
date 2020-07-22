<?php

namespace App\Exports;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\User;
use App\Models\CrmConfig;

class ClientExport extends ExcelExporter implements WithMapping, WithHeadings, ShouldAutoSize
{

    //这里是导出来的文件的名字和格式
    protected $fileName = '用户列表.xlsx';
    //这里是excel的标题
    public function headings(): array
    {
        return [
            '编号',
            '会员名称',
            '手机号',
            '所属销售人员',
            '客户类型',
            '跟进状态',
            '最后跟进时间',
        ];
    }

    public function map($client): array
    {	//这里是字段的值 如果是主表的数据 直接对象的形式就可以写出来
        //如果是关联的表的数据 可以通过data_get()去渲染
        //其他部分是枚举类字段值的语义化
        $client_status = CrmConfig::getKeyValue('client_status');
        $follow_status = CrmConfig::getKeyValue('follow_status');

        $client_status[0] = $follow_status[0] = '';
        if(empty($client->status)){
            $client->status = 0;
        }
        if(empty($client->employee_status)){
            $client->employee_status = 0;
        }
        return [
            $client->id,
            $client->name,
            $client->mobile,
            User::Users()[$client->employee_id],
            $client_status[$client->status],
            $follow_status[$client->employee_status],
            $client->last_updated_at,
        ];
    }



}
