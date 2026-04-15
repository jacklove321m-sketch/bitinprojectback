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
        <label class="layui-form-label">订单ID</label>
        <div class="layui-input-inline" style="width: 200px">
            <input class="layui-input" name="orderid" type="text" value="" placeholder="请输入订单ID">
        </div>
    </div>
    
     <div class="layui-inline">
    <label class="layui-form-label">状态</label>
        <div class="layui-input-inline" style="width:90px;">
            <select name="status">
                <option value="">全部</option>
                <option value="1">已结算</option>
                <option value="0">未结算</option>
            </select>
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
      <script type="text/html" id="statustml">
      
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'已结算'+'</span>' : '' }}
        @{{d.status==0 ? '<span class="layui-badge layui-bg-red">'+'未结算'+'</span>' : '' }}

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
                ,url: '{{url('admin/dual/order_lists')}}' //数据接口
                ,page: true //开启分页
                // ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', minWidth:40, sort: true}
                     
                    ,{field: 'user_id', title: '用户ID', minWidth: 80}
                     ,{field: 'day', title: '持仓期限(天)', minWidth:110}
                  //  ,{field: 'name', title: '量化名称', minWidth:200}
                    ,{field: 'orderid', title: '订单ID', minWidth:200}
                    ,{field: 'order_rate', title: '回报率%', minWidth:110}
                   
                    ,{field: 'amount', title: '购买金额', minWidth:110}
                    ,{field: 'todayincome', title: '今日收益', minWidth:110}
                    ,{field: 'totalincome', title: '总收益', minWidth:110}
                  //,{field: 'currency_id', title: '购买消耗币种', minWidth:110, templet: '#statusCurreny'}
                     ,{field: 'created', title: '创建时间', minWidth:110}
                       ,{field: 'expire', title: '到期时间', minWidth:110}
                        ,{field: 'status', title: '状态', minWidth:80, templet: '#statustml'}
                   //,{title:'操作',width:80,toolbar: '#barorderList'}
                ]]
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
