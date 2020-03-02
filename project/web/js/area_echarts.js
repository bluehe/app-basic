$(window).on('load', function () {
    b();

    function b() {
        var h = echarts.init(document.getElementById("map_1"));
        var f = [{
            name: "南京",
            value: 369
        }];
        var g = {
            "南京": [118.78, 32.04],
        };
        var a = function (c) {
            var i = [];
            for (var e = 0; e < c.length; e++) {
                var d = g[c[e].name];
                if (d) {
                    i.push({
                        name: c[e].name,
                        value: d.concat(c[e].value)
                    })
                }
            }
            return i
        };
        option = {
            tooltip: {
                trigger: "item",
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
            series: [{
                name: "消费金额",
                type: "scatter",
                coordinateSystem: "geo",
                data: a(f),
                symbolSize: function (c) {
                    return c[2] / 15
                },
                label: {
                    normal: {
                        formatter: "{b}",
                        position: "right",
                        show: false
                    },
                    emphasis: {
                        show: true
                    }
                },
                itemStyle: {
                    normal: {
                        color: "#ffeb7b"
                    }
                }
            }]
        };
        h.setOption(option);
        window.addEventListener("resize", function () {
            h.resize()
        })
    }
});