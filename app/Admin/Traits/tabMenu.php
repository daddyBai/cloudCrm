<?php

namespace App\Admin\Traits;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

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

        if(array_key_exists($target,$menuList)){
            return $menuList[$target];
        }else{
            return $menuList;
        }
    }

    public static function tabMenuList($id){
        $curl = explode("?",Request::getRequestUri())[0];
        $menuList = self::menuList($id);

        if(strstr($curl,'clue')){
            unset($menuList['放贷']);
            unset($menuList['回访']);
        }

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
