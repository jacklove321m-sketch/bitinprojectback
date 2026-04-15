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
        
       <!-- <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>量化币种</label>
            <div class="layui-input-block">
                <select name='currency' id="currency">
                    <option value='1' class='layui-option'>BTC</option>
                    <option value='2' class='layui-option'>ETH</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>类型</label>
            <div class="layui-input-block">
                 <select name='type'>
                    <option value='call' class='layui-option'>看涨（call）</option>
                    <option value='put' class='layui-option'>看跌（put）</option>
                </select>
            </div>
            
             <input type="checkbox" name="open_lever" value="1" title="杠杆合约" @if (isset($currency_match)) {{ $currency_match->open_lever == 1 ? 'checked' : '' }} @else checked @endif>
                            <input type="checkbox" name="open_microtrade" value="1" title="期权" @if (isset($currency_match)) {{ $currency_match->open_microtrade == 1 ? 'checked' : '' }} @else checked @endif>
                            <input type="checkbox" name="open_coin_trade" value="1" title="币币交易" @if (isset($currency_match)) {{ $currency_match->open_coin_trade == 1 ? 'checked' : '' }} @else checked @endif>
                            <input type="checkbox" name="coin_trade_success" value="1" title="币币交易撮合开关" @if (isset($currency_match)) {{ $currency_match->coin_trade_success == 1 ? 'checked' : '' }} @else checked @endif>
        </div>  -->
        
         <div class="layui-form-item">
                        <label for="currency_id" class="layui-form-label">投资组合</label>
                        <div class="layui-input-block">
                              @foreach ($currencies as $currency)
                            <input type="checkbox" name="invest[]" value="{{$currency->name}}" title="{{ $currency->name }}">
                            
                             @endforeach
                           

                        </div>
                    </div>
        
        
        
        
         <div class="layui-form-item">
            <label class="layui-form-label">天数</label>
            <div class="layui-input-block">
                <input type="number" step="any" name="days" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>起始回报率%</label>
            <div class="layui-input-block">
                <input type="number" name="rate" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>最大回报率%</label>
            <div class="layui-input-block">
                <input type="number" step="any" name="ratemax" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>起始金额</label>
            <div class="layui-input-block">
                 <input type="number" name="amount" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>最大金额</label>
            <div class="layui-input-block">
                 <input type="number" name="amax" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        
        
        
         <div class="layui-form-item">
            <label class="layui-form-label">次数</label>
            <div class="layui-input-block">
                <input type="text"  name="total_number" autocomplete="off" placeholder="" class="layui-input" value="">
                
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label">手续费</label>
            <div class="layui-input-block">
                <input type="text"  name="commission" autocomplete="off" placeholder="" class="layui-input" value="">
                
            </div>
        </div>
        
         <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>热门</label>
            <div class="layui-input-block">
                 <select name='hot'>
                    <option value='1' class='layui-option'>是</option>
                    <option value='0' class='layui-option'>否</option>
                </select>
            </div>
         </div>
        
         <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>违约金比例%</label>
            <div class="layui-input-block">
                 <input type="number" name="liquidateddamages" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="saveDual_submit">立即提交</button>
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
            form.on('submit(saveDual_submit)', function(data){
                var data = data.field;
               
                $.ajax({
                    url:'{{url('/admin/dual/saveDual')}}'
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
       /* 
        getNowPrice();
        setInterval(()=>{
            getNowPrice()
        },5000)
        */
        //获取实时价格
        function getNowPrice(){
            const val = $("#currency option:selected").val()

            $.ajax({
                url:'{{url('/admin/dual/getnewprice')}}'
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