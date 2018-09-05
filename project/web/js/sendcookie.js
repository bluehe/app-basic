 function addCookie(name, expiresHours) {
        var date = new Date();
        var value=date.getTime();
        var cookieString = name + "=" + escape(value);
        //判断是否设置过期时间,0代表关闭浏览器时失效  
        if (expiresHours > 0) {       
            date.setTime( value + expiresHours * 1000);
            cookieString = cookieString + ";expires=" + date.toUTCString();
        }
        document.cookie = cookieString;
    }
    function getCookieValue(name) {
        var strCookie = document.cookie;
        var arrCookie = strCookie.split("; ");
        for (var i = 0; i < arrCookie.length; i++) {
            var arr = arrCookie[i].split("=");
            if (arr[0] == name) {
                return unescape(arr[1]);
                break;
            }
        }
    }
    //开始倒计时  
    function settime(obj,title='重新获取验证码') {
        var date = new Date();
        var value=date.getTime();
        var countdown = getCookieValue("secondsremained_login") ? getCookieValue("secondsremained_login") : 0;
        var left=60-Math.floor((value-countdown)/1000);
        if (countdown === 0||left<=0) {
            obj.removeAttr("disabled");
            obj.val(title);
            return;
        } else {            
            obj.attr("disabled", true);
            obj.val(left + "秒后重新获取");
        }
        setTimeout(function () {
            settime(obj,title)
        }, 1000) //每1000毫秒执行一次
    }
    