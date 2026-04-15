@extends('admin._layoutNew')

@section('page-head')
    <style>
        .layui-form-label {
            width:90px;
        }
        .layui-input-block {
            margin-left: 120px;
        }
    </style>
@endsection

@section('page-content')
    <form class="layui-form">
        <div class="layui-form-item">
           
            <div class="layui-input-block">
                 
                <input type="hidden" name="id" placeholder="" disabled="disabled" class="layui-input" value="{{$results->id}}">
            </div>
        </div>
        
          <div class="layui-form-item">
            <label class="layui-form-label">期限</label>
            <div class="layui-input-block">
                 <input type="number" name="days" autocomplete="off" placeholder="" class="layui-input" value="{{$results->days}}">
            </div>
        </div>
        
          <div class="layui-form-item">
            <label class="layui-form-label">利率%</label>
            <div class="layui-input-block">
                <input type="number" step="any" name="bilv" autocomplete="off" placeholder="" class="layui-input" value="{{$results->bilv}}" lay-verify="required">
            </div>
        </div>
        
         <div class="layui-form-item">
            <label class="layui-form-label">验资百分比</label>
            <div class="layui-input-block">
                <input type="text"  name="percent" autocomplete="off" placeholder="" class="layui-input" value="{{$results->percent}}" lay-verify="required">
            </div>
        </div>
        
         <div class="layui-form-item">
            <label class="layui-form-label">滞纳金百分比</label>
            <div class="layui-input-block">
                  <input type="text" name="opaymentlv" autocomplete="off" placeholder="" class="layui-input" value="{{$results->opaymentlv}}" lay-verify="required">
            </div>
        </div>
        
      
        <div class="layui-form-item">
            <label class="layui-form-label">总服务费</label>
            <div class="layui-input-block">
                 <input type="number" name="fwamount" autocomplete="off" placeholder="" class="layui-input" value="{{$results->fwamount}}" lay-verify="required">
            </div>
        </div>
        
       
      
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="saveLending_submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(saveLending_submit)', function(data){
                var data = data.field;
                // data.start_time=data.start_time.replace("T"," ")
                // data.end_time=data.end_time.replace("T"," ")

                $.ajax({
                    url:'{{url('/admin/lending/editLending')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='ok'){
                            layer.msg('操作成功');
                            
                             parent.layer.close(index);
                             parent.window.location.reload();
                            
                        }if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            layer.msg(res.message);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
        
        
        
        
        //获取实时价格
        function getNowPrice(){
            const val = $("#currency option:selected").val()

            $.ajax({
                url:'{{url('/admin/lending/getnewprice')}}'
                ,type:'get'
                ,dataType:'json'
                ,data : 'currency=' +val
                ,success:function(res){
                    if (res.message.data.length) {
                        const value = res.message.data[0]
                        $("#nowPrice").val(value.price)
                    }
                }
            });
        }
    </script>

@endsection