@extends('admin._layoutNew')

@section('page-head')
<style>
    p.percent {
        text-align: right;
        margin-right: 10px;
    }
    p.percent::after {
        content: '%';
    }
</style>
@endsection

@section('page-content')

<div class="layui-form">
    <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加AI项目','{{url('admin/ai/add')}}')">添加AI项目</button>
    
    <!--搜索-->
    <div class="layui-inline">
        <!--<input name="user_id" type="hidden" value="{{Request::get('user_id')}}">-->
        
        <label class="layui-form-label">状态</label>
        <div class="layui-input-inline" style="width:90px;">
            <select name="status">
                <option value="">全部</option>
                <option value="1">进行中</option>
                <option value="0">已结束</option>
            </select>
        </div>
    </div>
   <!-- <div class="layui-inline">
        <label class="layui-form-label">币种</label>
        <div class="layui-input-inline" style="width:90px;">
            <select name="currency_id">
                <option value="">全部</option>
                <option value="1">BTC</option>
                <option value="2">ETH</option>
            </select>
        </div>
    </div>  -->
    <div class="layui-inline" style="margin-left: 10px">
        <button class="layui-btn" lay-submit lay-filter="submit">查询</button>
    </div>
</div>
     
    
    <table id="demo" lay-filter="test"></table>
@endsection

@section('scripts')
<script id="barOpe" type="text/html">
    <button class="layui-btn layui-btn-xs" lay-event="edit">编辑</button>
     <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
</script>    
    
    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'进行中'+'</span>' : '' }}
        @{{d.status==0 ? '<span class="layui-badge layui-bg-red">'+'已结束'+'</span>' : '' }}

    </script>
     <script type="text/html" id="statushot">
        @{{d.hot==1 ? '<span class="layui-badge layui-bg-green">'+'是'+'</span>' : '' }}
        @{{d.hot==0 ? '<span class="layui-badge layui-bg-red">'+'否'+'</span>' : '' }}

    </script>
    <script>
        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            var demo = table.render({
                elem: '#demo'
                ,toolbar: '#toolbar'
                ,url: '{{url('admin/ai_list')}}' //数据接口
                ,page: true //开启分页 
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'id', minWidth:60, sort: true}
                    ,{field: 'invest', title: 'AI名称', minWidth:200}
                    ,{field: 'name', title: '会员等级', minWidth:200}
                    ,{field: 'days', title: '运行时长', minWidth:100}
                    ,{field: 'rate', title: '最大收益率%', minWidth: 115}
                    ,{field: 'ratemax', title: '最大回撤率%', minWidth: 115}
                    ,{field: 'amount', title: '投入金额', minWidth:100}
                    ,{field: 'amax', title: '区间最低价', minWidth:100}
                    ,{field: 'purchased_number', title: '人数', minWidth:100}
                    ,{field: 'commission', title: '最大服务费', minWidth:100}
                //    ,{field: 'liquidateddamages', title: '违约金比例%', minWidth:110} 
                    ,{field: 'created', title: '创建时间', minWidth:110}
                    ,{field: 'status', title: '状态', minWidth:60, templet: '#statustml'}
                    ,{field: 'hot', title: '热门', minWidth:60, templet: '#statushot'}
                    ,{fixed: 'right', title: '操作',  minWidth:180,toolbar: '#barOpe'}
                    //,{title:'操作',width:80,toolbar: '#barDemo'}
                ]]
            });
            
            // 监听工具条
            table.on('tool(test)', function (obj) {
                var layEvent = obj.event
                    ,data = obj.data
                if (layEvent === 'edit') {
                    // layer.open({
                    //     type: 2
                    //     ,title: '编辑'
                    //     ,content: '/admin/ai/editaiView' + '?id=' + data.id
                    //     ,area: ['960px', '650px']
                    // });
                    var index = layer.open({
                        title: '编辑'
                        ,type: 2
                        ,content: '{{url('/admin/ai/editAiView')}}?id=' + data.id
                        ,area: ['960px', '500px']
                    });
                }
                
                // if(obj.event === 'edit'){
                //     layer_show('编辑双币项目','{{url('admin/dual/editDualView')}}?id='+data.id);
                // }
                 if (layEvent === 'delete') { //删除
                    layer.confirm('真的要删除吗？', function (index) {
                        //向服务端发送删除指令
                        $.ajax({
                            url: "/admin/ai/del",
                            //?id=" + data.id,
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'ok') {
                                    layer.msg('操作成功');
                                     obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                    layer.close(index);
                                } else {
                                    layer.close(index);
                                    layer.alert(res.message);
                                }
                            }
                        });
                    });
                }   
                
                
            });
            
            // //监听提交
            // form.on('submit(mobile_search)', function(data){
            //     var id = data.field.id;
            //     table.reload('demo',{
            //         where:{currency_id:id},
            //         page: {curr: 1}         //重新从第一页开始
            //     });
            //     return false;
            // });
            form.on('submit(submit)', function (data) {
                var option = {
                    where: data.field
                }
                // console.log(21213231,option)
                demo.reload(option);
            });
            
            // table.on('tool(test)', function(obj){
            //     var data = obj.data;
            //     if(obj.event === 'del'){
            //         layer.confirm('真的删除行么', function(index){
            //             $.ajax({
            //                 url:'{{url('admin/currency_del')}}',
            //                 type:'post',
            //                 dataType:'json',
            //                 data:{id:data.id},
            //                 success:function (res) {
            //                     if(res.type == 'error'){
            //                         layer.msg(res.message);
            //                     }else{
            //                         obj.del();
            //                         layer.close(index);
            //                     }
            //                 }
            //             });


            //         });
            //     } else if(obj.event === 'edit'){
            //         layer_show('编辑币种','{{url('admin/currency_add')}}?id='+data.id);
            //     } else if (obj.event == 'execute'){
            //         layer.confirm('确定执行上币脚本？', function(index){
            //             $.ajax({
            //                 url:'{{url('admin/currency_execute')}}',
            //                 type:'post',
            //                 dataType:'json',
            //                 data:{id:data.id},
            //                 success:function (res) {
            //                     layer.msg(res.message);
            //                 }
            //             });
            //         });
            //     } else if (obj.event == 'match') {
            //         layer.open({
            //             title: '交易对管理'
            //             ,type: 2
            //             ,content: '/admin/currency/match/' + data.id
            //             ,area: ['960px', '600px']
            //         });
            //     } else if(obj.event == 'deposit'){
            //         layer.open({
            //             title: '质押管理'
            //             ,type: 2
            //             ,content: '/admin/currency/deposit/' + data.id
            //             ,area: ['960px', '600px']
            //         });
            //     }
            // });
            
        //     //监听是否显示操作
        //     form.on('switch(isDisplay)', function(obj){
        //         var id = this.value;
        //         $.ajax({
        //             url:'{{url('admin/currency_display')}}',
        //             type:'post',
        //             dataType:'json',
        //             data:{id:id},
        //             success:function (res) {
        //                 if(res.error != 0){
        //                     layer.msg(res.message);
        //                 }
        //             }
        //         });
        //     });
        // //监听是否购买保险操作
        //     form.on('switch(isInsurancable)', function(obj){
        //         var id = this.value;
        //         $.ajax({
        //             url:'{{url('admin/is_insurancable')}}',
        //             type:'post',
        //             dataType:'json',
        //             data:{id:id},
        //             success:function (res) {
        //                 if(res.error != 0){
        //                     layer.msg(res.message);
        //                 }
        //             }
        //         });
        //     });
            
            // //监听工具条
            // table.on('tool(adminList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //     var data = obj.data; //获得当前行数据
            //     var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //     var tr = obj.tr; //获得当前行 tr 的DOM对象

            //     if(layEvent === 'del'){ //删除
            //         layer.confirm('真的要删除吗？', function(index){
            //             //向服务端发送删除指令
            //             $.ajax({
            //                 url:'/admin/manager/delete',
            //                 type:'post',
            //                 dataType:'json',
            //                 data:{id:data.id},
            //                 success:function(res){
            //                     if(res.type=='ok'){
            //                         obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
            //                         layer.msg(res.message);
            //                         layer.close(index);
            //                     }else{
            //                         layer.close(index);
            //                         layer.alert(res.message);
            //                     }
            //                 }
            //             });
            //         });
            //     } else if(layEvent === 'edit'){ //编辑
            //         //do something
            //             layer_show('修改管理员', '/admin/manager/add?id=' + data.id);

            //     }
            // });
            
            
            
            
            
            // //是否开启币币划转
            // form.on('switch(is_transfer)', function(obj){
            //     var id = this.value;
            //     $.ajax({
            //         url:'{{url('admin/is_transfer')}}',
            //         type:'post',
            //         dataType:'json',
            //         data:{id:id},
            //         success:function (res) {
            //             if(res.error != 0){
            //                 layer.msg(res.message);
            //             }
            //         }
            //     });
            // });
            
            // table.on('toolbar(test)', function (obj) {
            //     switch (obj.event) {
            //         case 'add':
            //             layer.open({
            //                 title: '添加币种'
            //                 ,type: 2
            //                 ,content: '/admin/currency_add'
            //                 ,area: ['480px', '650px']
            //             });
            //             break;
            //         default:
            //             break;
            //     }
            // });

            // table.on('tool(test)', function(obj){
            //     var data = obj.data;
            //     if(obj.event === 'del'){
            //         layer.confirm('真的删除行么', function(index){
            //             $.ajax({
            //                 url:'{{url('admin/currency_del')}}',
            //                 type:'post',
            //                 dataType:'json',
            //                 data:{id:data.id},
            //                 success:function (res) {
            //                     if(res.type == 'error'){
            //                         layer.msg(res.message);
            //                     }else{
            //                         obj.del();
            //                         layer.close(index);
            //                     }
            //                 }
            //             });


            //         });
            //     } else if(obj.event === 'edit'){
            //         layer_show('编辑币种','{{url('admin/currency_add')}}?id='+data.id);
            //     } else if (obj.event == 'execute'){
            //         layer.confirm('确定执行上币脚本？', function(index){
            //             $.ajax({
            //                 url:'{{url('admin/currency_execute')}}',
            //                 type:'post',
            //                 dataType:'json',
            //                 data:{id:data.id},
            //                 success:function (res) {
            //                     layer.msg(res.message);
            //                 }
            //             });
            //         });
            //     } else if (obj.event == 'match') {
            //         layer.open({
            //             title: '交易对管理'
            //             ,type: 2
            //             ,content: '/admin/currency/match/' + data.id
            //             ,area: ['960px', '600px']
            //         });
            //     } else if(obj.event == 'deposit'){
            //         layer.open({
            //             title: '质押管理'
            //             ,type: 2
            //             ,content: '/admin/currency/deposit/' + data.id
            //             ,area: ['960px', '600px']
            //         });
            //     }
            // });

            
        });
    </script>

@endsection
