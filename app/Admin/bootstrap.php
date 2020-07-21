<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Encore\Admin\Form::forget(['map', 'editor']);

use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Displayers\Actions;

Admin::navbar(function (Encore\Admin\Widgets\Navbar $navbar){
    $navbar->right(view('admin.tools.refreshRedis'));
});

Grid::init(function (Grid $grid){


    // 全局规则
    $grid->actions(function (Actions $actions){
        $actions->disableView();
    });

    /**
     * 员工禁用规则
     * 导出/删除/多行选择
     */
    if(Admin::user()->isRole('employee')){
        $grid->disableExport();
        $grid->actions(function (Actions $actions){
            $actions->disableDelete();

        });
    }

});

