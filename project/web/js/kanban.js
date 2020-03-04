$(window).on('load', function () {
    $(".loading").fadeOut()
});
$(document).ready(function () {
    var whei = $(window).width();
    // 判断
    // if (whei < 1024) whei = 1024
    // if (whei > 1920) whei = 1920
    $("html").css({
        fontSize: whei / 20
    });
    $(window).resize(function () {
        var whei = $(window).width();
        $("html").css({
            fontSize: whei / 20
        })
    });
});

var t = null;
t = setTimeout(time, 1000);

function time() {
    clearTimeout(t);
    dt = new Date();
    var y = dt.getFullYear();
    var mt = dt.getMonth() + 1;
    var day = dt.getDate();
    var h = dt.getHours();
    var m = dt.getMinutes();
    var s = dt.getSeconds();
    document.getElementById("showTime").innerHTML = y + "年" + mt + "月" + day + "日 " + h + "时" + m + "分" + s + "秒";
    t = setTimeout(time, 1000);
}

$(function () {
    $("#fullScreen").on("click", function () {
        var isFull = !!(document.webkitIsFullScreen || document.mozFullScreen ||
            document.msFullscreenElement || document.fullscreenElement
        ); //!document.webkitIsFullScreen都为true。因此用!!
        if (isFull == false) {
            //全屏
            fullScreen();
        } else {
            //退出全屏
            exitFullscreen();
        }
    })
})

//fullScreen()和exitScreen()有多种实现方式，此处只使用了其中一种
//全屏
function fullScreen() {
    var element = document.getElementById("dataview");
    if (element.requestFullscreen) {
        element.requestFullscreen();
    } else if (element.msRequestFullscreen) {
        element.msRequestFullscreen();
    } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
    } else if (element.webkitRequestFullscreen) {
        element.webkitRequestFullscreen();
    }
}

//退出全屏 
function exitFullscreen() {
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    }
}

//监听window是否全屏，并进行相应的操作,支持esc键退出
window.addEventListener("resize", function () {
    //全屏
    var isFull = !!(document.webkitIsFullScreen || document.mozFullScreen ||
        document.msFullscreenElement || document.fullscreenElement
    ); //!document.webkitIsFullScreen都为true。因此用!!
    if (isFull == false) {
        $("#fullScreen span").attr("class", "glyphicon glyphicon-fullscreen");
    } else {
        $("#fullScreen span").attr("class", "glyphicon glyphicon-resize-small");
    }
})

function bar() {
    var option = {
        tooltip: {
            trigger: "axis",
            axisPointer: {
                type: "shadow"
            }
        },
        grid: {
            left: "10",
            top: "10",
            right: "10",
            bottom: "0",
            containLabel: true
        },
        xAxis: [{
            type: "category",
            axisLine: {
                show: true,
                lineStyle: {
                    color: "rgba(255,255,255,.1)",
                    width: 1,
                    type: "solid"
                },
            },
            axisTick: {
                show: false,
            },
            axisLabel: {
                interval: 0,
                show: true,
                splitNumber: 15,
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: "12",
                },
            },
        }],
        yAxis: [{
            type: "value",
            boundaryGap: ['20%', '20%'],
            min: 0,
            axisLabel: {
                show: true,
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: "12",
                },
            },
            axisTick: {
                show: false,
            },
            axisLine: {
                show: true,
                lineStyle: {
                    color: "rgba(255,255,255,.1	)",
                    width: 1,
                    type: "solid"
                },
            },
            splitLine: {
                lineStyle: {
                    color: "rgba(255,255,255,.1)",
                }
            }
        }, {
            type: "value",
            opposite: true,
            min: 0,
            max: 100,
            axisLabel: {
                show: true,
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: "12",
                },
                formatter: '{value} %',
            },
            axisTick: {
                show: false,
            },
            axisLine: {
                show: true,
                lineStyle: {
                    color: "rgba(255,255,255,.1	)",
                    width: 1,
                    type: "solid"
                },
            },
            splitLine: {
                show: false,
                lineStyle: {
                    color: "rgba(255,255,255,.1)",
                }
            }
        }],
        series: []
    };
    return option;
}

function bar2() {
    var option = {
        tooltip: {
            trigger: "axis",
            axisPointer: {
                type: "shadow"
            }
        },
        legend: {
            top: "0%",
            textStyle: {
                color: "rgba(255,255,255,.5)",
                fontSize: "12",
            }
        },
        grid: {
            left: "10",
            top: "40",
            right: "10",
            bottom: "0",
            containLabel: true
        },
        xAxis: [{
            type: "category",
            axisLine: {
                show: true,
                lineStyle: {
                    color: "rgba(255,255,255,.1)",
                    width: 1,
                    type: "solid"
                },
            },
            axisTick: {
                show: false,
            },
            axisLabel: {
                interval: 0,
                show: true,
                splitNumber: 15,
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: "12",
                },
            },
        }],
        yAxis: [{
            type: "value",
            boundaryGap: ['20%', '20%'],
            min: 0,
            axisLabel: {
                show: true,
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: "12",
                },
            },
            axisTick: {
                show: false,
            },
            axisLine: {
                show: true,
                lineStyle: {
                    color: "rgba(255,255,255,.1	)",
                    width: 1,
                    type: "solid"
                },
            },
            splitLine: {
                lineStyle: {
                    color: "rgba(255,255,255,.1)",
                }
            }
        }, {
            type: "value",
            opposite: true,
            min: 0,
            max: 100,
            axisLabel: {
                show: true,
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: "12",
                },
                formatter: '{value} %',
            },
            axisTick: {
                show: false,
            },
            axisLine: {
                show: true,
                lineStyle: {
                    color: "rgba(255,255,255,.1	)",
                    width: 1,
                    type: "solid"
                },
            },
            splitLine: {
                show: false,
                lineStyle: {
                    color: "rgba(255,255,255,.1)",
                }
            }
        }],
        series: []
    };
    return option;
}

function pie() {
    var option = {
        tooltip: {
            trigger: "item",
            formatter: "{b}<br/> {c}家 ,{d}%",
            position: function (b) {
                return [b[0] + 10, b[1] - 10]
            }
        },
        series: []
    };
    return option;
}

function line() {
    var option = {
        tooltip: {
            trigger: "axis",
            axisPointer: {
                lineStyle: {
                    color: "#dddc6b"
                }
            }
        },
        legend: {
            top: "0%",
            textStyle: {
                color: "rgba(255,255,255,.5)",
                fontSize: "12",
            }
        },
        grid: {
            left: "10",
            top: "30",
            right: "10",
            bottom: "0",
            containLabel: true
        },
        xAxis: [{
            type: "category",
            boundaryGap: false,
            axisLabel: {
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: 12,
                },
            },
            axisLine: {
                lineStyle: {
                    color: "rgba(255,255,255,.2)"
                }
            },
        }, {
            axisPointer: {
                show: false
            },
            axisLine: {
                show: false
            },
            position: "bottom",
            offset: 20,
        }],
        yAxis: [{
            type: "value",
            title: {
                text: '数量（次）'
            },
            min: 0,
            axisTick: {
                show: false
            },
            axisLine: {
                lineStyle: {
                    color: "rgba(255,255,255,.1)"
                }
            },
            axisLabel: {
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: 12,
                },
            },
            splitLine: {
                lineStyle: {
                    color: "rgba(255,255,255,.1)"
                }
            }
        }, {
            type: "value",
            title: {
                text: '金额（万元）'
            },
            min: 0,
            axisTick: {
                show: false
            },
            axisLine: {
                lineStyle: {
                    color: "rgba(255,255,255,.1)"
                }
            },
            axisLabel: {
                textStyle: {
                    color: "rgba(255,255,255,.6)",
                    fontSize: 12,
                },
            },
            splitLine: {
                lineStyle: {
                    color: "rgba(255,255,255,.1)"
                }
            }
        }],
        series: []
    };
    return option;
}

function bmap() {
    var option = {
        tooltip: {
            trigger: "none",
            formatter: function (c) {
                if (typeof (c.value)[2] == "undefined") {
                    return c.name + " : " + c.value
                } else {
                    return c.name + " : " + c.value[2]
                }
            }
        },
        geo: {
            map: "china",
            label: {
                emphasis: {
                    show: false
                }
            },
            roam: false,
            itemStyle: {
                normal: {
                    areaColor: "#4c60ff",
                    borderColor: "#002097"
                },
                emphasis: {
                    areaColor: "#293fff"
                }
            }
        },
        series: []
    };
    return option;
}