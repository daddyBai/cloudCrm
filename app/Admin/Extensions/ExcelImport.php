<?php
namespace App\Admin\Extensions\Tools;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class ExcelImport extends AbstractTool
{
    public function script()
    {
        return <<<EOT
   $('.file-upload').on('change', function () {
        $('.file-upload-form').submit();
    });
EOT;
    }
    public function render()
    {
        Admin::script($this->script());
        if (Request::path() == 'loansarticles'){
            $url = 'loansnewsimport';
        }else {
            $url = 'cardnewsimport';
        }
        return view('admin.tools.excelimport')->with('url',$url);
    }
}
