<?php

namespace App\Admin\Traits;
use App\Models\Client;
use App\Models\Department;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Request;

trait tabMenu{

    public static function menuList($id, $target=""){

        $menuList = [
            '详细资料'=>"/admin/myClient/$id/edit",
            '活动'=>"/admin/records/$id",
            '附件'=>"/admin/files/$id",
            '呼叫记录'=>"/admin/dial/$id",
            '协作成员'=>"/admin/needHelp/$id",
            '放贷'=>"/admin/moneyOut/$id",
            '回访'=>"/admin/feedback/$id"
        ];

        // 线索
        $true_client = Client::query()
            ->where('id',$id)
            ->where('true_client',1)
            ->exists();

        if(! $true_client){
            unset($menuList['放贷']);
            unset($menuList['回访']);
            $menuList['详细资料'] = "/admin/clue/$id/edit";
        }else if(Client::query()
            ->where('id',$id)
            ->where('in_sea',1)
            ->exists()){
            unset($menuList['放贷']);
            unset($menuList['回访']);
            $menuList['详细资料'] = "/admin/sea/$id/edit";
        }

        if(array_key_exists($target,$menuList)){
            return $menuList[$target];
        }else{
            return $menuList;
        }
    }

    public static function tabMenuList($id){
        $curl = explode("?",Request::getRequestUri())[0];
        $menuList = self::menuList($id);



        $tab = new Tab();
        foreach ($menuList as $k => $m){
            if($m == $curl){
                $tab->addLink($k,$m,$active=true);
            }else{
                $tab->addLink($k,$m);
            }
        }
        return $tab->render();
    }



}
