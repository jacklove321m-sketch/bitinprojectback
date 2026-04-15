@extends('admin._layoutNew')

@section('page-head')
    <style>
        .layui-form-label {
            width:90px;
        }
        .layui-input-block {
            margin-left: 120px;
        }
        .reddot {
            position: absolute;
            right:5%;
            color: red;
        }
    </style>
@endsection

@section('page-content')
    <form class="layui-form" action="">
        
           <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>期限</label>
            <div class="layui-input-block">
                <input type="number" name="days" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>利率%</label>
            <div class="layui-input-block">
                <input type="number" name="bilv" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        
     
         
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>验资百分比</label>
            <div class="layui-input-block">
                 <input type="text" name="percent" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">滞纳金百分比</label>
            <div class="layui-input-block">
                 <input type="number" name="opaymentlv" autocomplete="off" placeholder="" class="layui-input" value="">
            </div>
        </div>
          <div class="layui-form-item">
            <label class="layui-form-label">总服务费</label>
            <div class="layui-input-block">
                 <input type="text" name="fwamount" autocomplete="off" placeholder="" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="saveLending_submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
    // var startTime =$('.start_timeinput').val();
    // var endTime =$('.end_timeinput').val();
    
    //     this.startTime = str_replace("T"," ",this.startTime);
    //     this.endTime = str_replace("T"," ",this.endTime);
        // 表单提交
       layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(saveLending_submit)', function(data){
                var data = data.field;
                 
                $.ajax({
                    url:'{{url('/admin/lending/saveLending')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });


        // layui.use(['form','laydate'],function () {
        //     var form = layui.form
        //         ,$ = layui.jquery
        //         ,laydate = layui.laydate
        //         ,index = parent.layer.getFrameIndex(window.name);
        //     //监听提交
        //     form.on('submit(demo1)', function(data){
        //         var data = data.field;
        //         $.ajax({
        //             url:'{{url('admin/user/add')}}'
        //             ,type:'post'
        //             ,dataType:'json'
        //             ,data : data
        //             ,success:function(res){
        //                 if(res.type=='error'){
        //                     layer.msg(res.message);
        //                 }else{
        //                     parent.layer.close(index);
        //                     parent.window.location.reload();
        //                 }
        //             }
        //         });
        //         return false;
        //     });
        // });
        
        getNowPrice();
        setInterval(()=>{
            getNowPrice()
        },5000)
        
        //获取实时价格
        function getNowPrice(){
            const val = $("#currency option:selected").val()

            $.ajax({
                url:'{{url('/admin/lending/getnewprice')}}'
                ,type:'get'
                ,dataType:'json'
                ,data : 'currency=' +val
                ,success:function(res){
                    const value = res.message.data[0]
                    $("#nowPrice").val(value.price)
                    
                    
                }
            });
        }
    </script>

@endsection