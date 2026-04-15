var localurl = 'tmallzn.cc',
    http = require('https'),
    schedule = require('node-schedule'),
	headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36"};

// 定时器，每3秒执行
schedule.scheduleJob('*/3 * * * * *', function(){
    var option = {
            host: localurl,
            timeout: 5000,
            path: '/api/currency/new_test',
            headers: headers
        };
    http.request(option, function (res) {
        var data = "";
        res.on("data", function (_data) {
            
        });
        res.on("end", function () {
            
        });
    }).end();
});