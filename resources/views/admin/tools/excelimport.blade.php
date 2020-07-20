<label class="btn btn-sm btn-danger"{{-- data-toggle="modal" data-target="#uploadModal"--}}>
    <i class="fa fa-stack-overflow"></i>&nbsp;&nbsp;数据导入
    <form action="{{$url}}" method="post" class="file-upload-form" enctype="multipart/form-data" pjax-container>
        <input type="file" name="files[]" class="hidden file-upload" multiple>
        {{ csrf_field() }}
    </form>
</label>
