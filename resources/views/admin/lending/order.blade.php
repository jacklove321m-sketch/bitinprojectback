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
    <!--<button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加双币项目','{{url('admin/dual/add')}}')">添加双币项目</button>-->
    
    <!--搜索-->
    <div class="layui-inline">
        <label class="layui-form-label">用户ID</label>
        <div class="layui-input-inline" style="width: 200px">
            <input class="layui-input" name="user_id" type="text" value="" placeholder="请输入用户ID">
        </div>
    </div>
    
     <div class="layui-inline">
        <label class="layui-form-label">订单编号</label>
        <div class="layui-input-inline" style="width: 200px">
            <input class="layui-input" name="htcode" type="text" value="" placeholder="请输入订单编号">
        </div>
    </div>
    
    <div class="layui-inline" style="margin-left: 10px;">
        <div class="layui-input-inline" style="width: 60px;">
            <button class="layui-btn" lay-submit="search" lay-filter="search"><i class="layui-icon">&#xe615;</i></button>
        </div>
    </div>
</div>
     
    
    <table id="orderList" lay-filter="test"></table>
@endsection


@section('scripts')

<script id="barOpe" type="text/html">
    <button class="layui-btn layui-btn-xs" lay-event="show">查看</button> 
    <button class="layui-btn layui-btn-xs" lay-event="cancle">还款</button>
</script>  

    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'已还款'+'</span>' : '' }}
        @{{d.status==0 ? '<span class="layui-badge layui-bg-red">'+'未还款'+'</span>' : '' }}

    </script>
    
     <script type="text/html" id="reviewstatus">
        @{{d.review_status==1 ? '<span class="layui-badge layui-bg-green">'+'未审核'+'</span>' : '' }}
        @{{d.review_status==2 ? '<span class="layui-badge layui-bg-red">'+'已审核'+'</span>' : '' }}
        @{{d.review_status==3 ? '<span class="layui-badge layui-bg-red">'+'驳回'+'</span>' : '' }}
    </script>
    
    <script type="text/html" id="statusCurreny">
        @{{d.currency_id==1 ? '<span class="">'+'BTC'+'</span>' : '' }}
        @{{d.currency_id==2 ? '<span class="">'+'ETH'+'</span>' : '' }}
        @{{d.currency_id==3 ? '<span class="">'+'USDT'+'</span>' : '' }}
    </script>
    
    <script type="text/html" id="img">

    @{{# if(d.signatureUrl){ }}
        <img src="@{{d.signatureUrl}}" style="background-color: #000;width: 80px" onmouseover="layer.tips('<div style=\'background-color: #000;\'><img style=\'max-width: 100px;\' src=@{{d.signatureUrl}}></div>',this,{tips: [1, '#000']});" onmouseout="layer.closeAll();">
    @{{# } }}

</script>
    <script>
        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            var orderList = table.render({
                elem: '#orderList'
                ,toolbar: '#toolbar'
                ,url: '{{url('admin/lending/order_lists')}}' //数据接口
                ,page: true //开启分页
                // ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: '订单ID', minWidth:110, sort: true}
                    ,{field: 'user_id', title: '用户ID', minWidth: 110}
                    ,{field: 'htcode', title: '订单编号', minWidth:200}
                    ,{field: 'yamount', title: '验资金额', minWidth:110}
                     ,{field: 'amount', title: '借币数量', minWidth:110}
                    ,{field: 'days', title: '借币期限', minWidth:120}
                 
                  //  ,{field: 'hk_type', title: '还款方式', minWidth:110}
                  //  ,{field: 'name', title: '放款机构', minWidth:110}
                    ,{field: 'lv_perday', title: '日币息率',  minWidth:120}
                    ,{field: 'fwamount', title: '总服务费',  minWidth:120}
                        ,{field: 'opaymentlv', title: '滞纳金率',  minWidth:120}
                        ,{field: 'opayment', title:'滞纳金',  minWidth:120}
                    ,{field: 'lixi', title: '币息', minWidth:110}
                    ,{field: 'status', title: '还款状态', minWidth:110, templet: '#statustml'}
                    ,{field: 'review_status', title: '审核状态', minWidth:110, templet: '#reviewstatus'}
                    ,{field: 'create_time', title: '借币时间', minWidth:110}
                    ,{field: 'end_time', title: '还币时间', minWidth:110}
                    ,{field:'signatureUrl', title:'手写签名', minWidth:120,templet:"#img"}
                     ,{fixed: 'right', title: '操作', toolbar: '#barOpe'}
                    // ,{title:'操作',width:80,toolbar: '#barorderList'}
                ]]
            });
            
            
             table.on('tool(test)', function(obj){
                var data = obj.data;
                 if(obj.event === 'show'){
                    layer_show('确认放款','{{url('admin/jiedai_show')}}?id='+data.id,800,600);
                } else if(obj.event === 'back'){
                    layer_show('退回申请','{{url('admin/adjust_account')}}?id='+data.id,800,600);
                }else if(obj.event === 'cancle'){
                    layer_show('还款','{{url('admin/adjust_account')}}?id='+data.id,800,600);
                }
                
            });
            

            //监听提交
            form.on('submit(search)', function (data) {
                orderList.reload({
                    where: data.field
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                return false;
            });
        });
    </script>

@endsection
