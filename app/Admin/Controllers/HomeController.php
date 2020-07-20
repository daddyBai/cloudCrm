<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\DB;

class HomeController extends BaseController
{
    public function index(Content $content)
    {

        return $content
            ->title('主面板')
            ->row(Dashboard::title())
            ->row(function (Row $row) {
                $menuList = DB::table('admin_menu')->get()->chunk(4);
                $color = ['black','maroon','orange','purple','teal','navy','red','yellow','green','aqua','light-blue'];
                foreach ($menuList as $key => $menu){
                    $row->column(3, function (Column $column) use ($menu, $color) {
                        foreach ($menu as $m){
                            $infoBox = new InfoBox($m->title, $m->icon, $color[array_rand($color)], $m->uri, 500);
                            $column->append($infoBox->render());
                        }
                    });
                }
            });
    }


}
