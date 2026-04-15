@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content') 
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">借币信息</label>
            <div class="layui-input-block">
               <table class="layui-table">
                <tbody>
                    <tr>
                        <td>
                            用户ID：{{$wallet_out->user_id}}
                        </td>
                        <td>
                            订单编号：{{$wallet_out->htcode}}
                        </td>
                    </tr>
                     <tr>
                        <td>
                            验资金额：{{$wallet_out->yamount}}
                        </td>
                        <td>
                            总服务费：{{$wallet_out->fwamount}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            借币数量：{{$wallet_out->amount}}
                        </td>
                        <td>
                            借币期限：{{$wallet_out->days}}
                        </td>
                    </tr>
                    <tr>  
                        <td>
                            日币息率：{{$wallet_out->lv_perday}}
                        </td>
                        <td>
                            手写签名：<img src='{{$wallet_out->signatureUrl}}'  style="background-color: #000;width: 80px">
                        </td>
                    </tr>
                    <tr>
                         <td>
                            签名：{{$wallet_out->signature}}
                        </td>
                         
                         <td>
                            币息：{{$wallet_out->lixi}}
                        </td>
                        
                    </tr>
                   
                   
                    
                    <tr>
                        <td>
                            申请时间：{{$wallet_out->create_time}}
                        </td>
                        <td>
                            当前状态：@if($wallet_out->review_status==1) 提交申请
								     @elseif($wallet_out->review_status==2) 放款成功
								     @elseif($wallet_out->review_status==3) 放款驳回
								    @else
                                    @endif
                        </td>
                    </tr>
                     
                </tbody>
            </table>
            </div>
        </div>
        <!--
        <div class="layui-form-item">
            <label class="layui-form-label">反馈信息</label>
            <div class="layui-input-block">
               <textarea name="notes" id="" cols="90" rows="5">{{$wallet_out->notes}}</textarea>
            </div>
        </div>  -->
        @if($wallet_out->status==1)
        <!--<div class="layui-form-item">-->
        <!--    <label class="layui-form-label">安全验证码</label>-->
        <!--    <div class="layui-input-inline">-->
        <!--        <input type="text" name="verificationcode" placeholder="" autocomplete="off" class="layui-input">-->
        <!--    </div>-->
        <!--    <button type="button" class="layui-btn layui-btn-primary" id="get_code">获取验证码</button>-->
        <!--</div>-->
        @endif
        <input type="hidden" name="id" value="">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <input type="hidden" name='id' value='{{$wallet_out->id}}'>
                 <input type="hidden" name='user_id' value='{{$wallet_out->user_id}}'>
                <input type="hidden" name='amount' value='{{$wallet_out->amount}}'>
                @if($wallet_out->review_status==1)
                <button class="layui-btn" lay-submit="" lay-filter="demo1" name='method' value="done">确认放款</button>
                <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="demo2">退回申请</button>
                @endif
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
          
          /*  $('#get_code').click(function () {
                var that_btn = $(this);
                $.ajax({
                    url: '/admin/safe/verificationcode'
                    ,type: 'GET'
                    ,success: function (res) {
                        if (res.type == 'ok') {
                            that_btn.attr('disabled', true);
                            that_btn.toggleClass('layui-btn-disabled');
                        }
                        layer.msg(res.message, {
                            time: 3000
                        });
                    }
                    ,error: function () {
                        layer.msg('网络错误');
                    }
                });
            });*/
            //监听提交
            form.on('submit(demo1)', function(data) {
                var data = data.field;
                console.log(data);
               //if (data.verificationcode == '') {
                //    layer.msg('请填写安全验证码');
                //    return false;
               // }
                layer.confirm('确定允许借款?', function (index) {
                    var loading = layer.load(1, {time: 30 * 1000});
                    layer.close(index);
                    $.ajax({
                        url: '{{url('admin/jiedai_done')}}'+'?method=done'
                        ,type: 'post'
                        ,dataType: 'json'
                        ,data : data
                        ,success: function(res) {
                            if (res.type=='error') {
                                layer.msg(res.message);
                            } else {
                                layer.msg(res.message);
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }
                        }
                        ,complete: function () {
                            layer.close(loading);
                        }
                    });
                });
                return false;
            });
            form.on('submit(demo2)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/jiedai_done')}}'
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
    </script>

@endsection