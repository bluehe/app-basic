function i() {
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
            bottom: "10",
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
            // reversedStacks: false,
            // stackLabels: {
            //     enabled: true,
            // },
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

function j() {
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
            bottom: "10",
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
            // reversedStacks: false,
            // stackLabels: {
            //     enabled: true,
            // },
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

function k() {
    var option = {
        tooltip: {
            trigger: "axis",
            axisPointer: {
                type: "shadow"
            }
        },
        grid: {
            left: "0%",
            top: "10px",
            right: "0%",
            bottom: "4%",
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
            // reversedStacks: false,
            // stackLabels: {
            //     enabled: true,
            // },
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
    return option;
}

function m() {
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