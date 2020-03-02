function n() {
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
            bottom: "10",
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
            opposite: true,
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

function p() {

    var option = {
        tooltip: {
            trigger: "item",
            formatter: "{b}<br/> {c}家 ({d}%)",
            position: function (b) {
                return [b[0] + 10, b[1] - 10]
            }
        },
        legend: {
            top: "90%",
            itemWidth: 10,
            itemHeight: 10,

            textStyle: {
                color: "rgba(255,255,255,.5)",
                fontSize: "12",
            }
        },
        series: []
    };
    return option
}