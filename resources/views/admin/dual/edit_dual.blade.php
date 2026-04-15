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
                        <label for="currency_id" class="layui-form-label">投资组合</label>
                           <input type="hidden" name="id" placeholder="" disabled="disabled" class="layui-input" value="{{$results->id}}">
                        <div class="layui-input-block">
                              @foreach ($currencies as $currency)
                            <input type="checkbox" name="invest[]" value="{{$currency->name}}" title="{{ $currency->name }}" @if (isset($invest)) {{ stristr($invest,$currency->name) ? 'checked' : '' }} @else checked @endif>
                            
                             @endforeach
                            

                        </div>
                    </div>
        
       <!-- <div class="layui-form-item">
            <label class="layui-form-label">量化名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="{{$results->name}}">
             
            </div>
        </div>-->
         <div class="layui-form-item">
            <label class="layui-form-label">状态(停止用户购买项目)</label>
            <div class="layui-input-block">
                <select name="status" lay-verify="required">
                    <option value="0" {{ ($results->status) == 0 ? 'selected' : '' }} >结束</option>
                    <option value="1" {{ ($results->status) == 1 ? 'selected' : '' }} >进行</option>
                </select>
            </div>
        </div>
        
    <div class="layui-form-item">
            <label class="layui-form-label">天数</label>
            <div class="layui-input-block">
                <input type="number" step="any" name="days" autocomplete="off" placeholder="" class="layui-input" value="{{$results->days}}" lay-verify="required">
            </div>
        </div>
        
        
        <div class="layui-form-item">
            <label class="layui-form-label">起始回报率%</label>
            <div class="layui-input-block">
                <input type="number" step="any" name="rate" autocomplete="off" placeholder="" class="layui-input" value="{{$results->rate}}" lay-verify="required">
            </div>
        </div>
        
         <div class="layui-form-item">
            <label class="layui-form-label">最大回报率%</label>
            <div class="layui-input-block">
                <input type="number" step="any" name="ratemax" autocomplete="off" placeholder="" class="layui-input" value="{{$results->ratemax}}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">起始金额</label>
            <div class="layui-input-block">
                 <input type="number" name="amount" autocomplete="off" placeholder="" class="layui-input" value="{{$results->amount}}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大金额</label>
            <div class="layui-input-block">
                 <input type="number" name="amax" autocomplete="off" placeholder="" class="layui-input" value="{{$results->amax}}">
            </div>
        </div>
        
          <div class="layui-form-item">
            <label class="layui-form-label">次数</label>
            <div class="layui-input-block">
                <input type="text"  name="total_number" autocomplete="off" placeholder="" class="layui-input" value="{{$results->total_number}}">
                
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label">手续费</label>
            <div class="layui-input-block">
                <input type="text"  name="commission" autocomplete="off" placeholder="" class="layui-input" value="{{$results->commission}}">
                
            </div>
        </div>
        
         <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>热门</label>
            <div class="layui-input-block">
                 <select name='hot'>
                        <option value="0" {{ ($results->hot) == 0 ? 'selected' : '' }} >否</option>
                    <option value="1" {{ ($results->hot) == 1 ? 'selected' : '' }} >是</option>
                  
                </select>
            </div>
         </div>
        
        
         <div class="layui-form-item">
            <label class="layui-form-label">违约金比例%</label>
            <div class="layui-input-block">
                 <input type="number" name="liquidateddamages" autocomplete="off" placeholder="" class="layui-input" value="{{$results->liquidateddamages}}">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="saveDual_submit">立即提交</button>
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
            form.on('submit(saveDual_submit)', function(data){
                var data = data.field;
                // data.start_time=data.start_time.replace("T"," ")
                // data.end_time=data.end_time.replace("T"," ")

                $.ajax({
                    url:'{{url('/admin/dual/editDual')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='ok'){
                            layer.msg('操作成功');
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
                    if (res.message.data.length) {
                        const value = res.message.data[0]
                        $("#nowPrice").val(value.price)
                    }
                }
            });
        }
    </script>

@endsection