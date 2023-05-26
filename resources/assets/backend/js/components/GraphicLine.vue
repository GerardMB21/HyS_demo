<template>
    <div v-if="show_graphic">
        <apexchart type="line" height="350" :options="chartOptions" :series="series"></apexchart>
    </div>
</template>

<script>
    import EventBus from '../event-bus';

    export default {
        data() {
            return {
                chartOptions: {
                    chart: {
                        type: 'line',
                        title: {
                            text: 'Productos a expirar',
                            align: 'left'
                        },
                        grid: {
                            row: {
                                colors: [
                                    '#f3f3f3',
                                    'transparent'
                                ],
                                opacity: 0.5
                            },
                        },
                    },
                    xaxis: {
                        categories: [
                            1991,
                            1992,
                            1993,
                            1994,
                            1995,
                            1996,
                            1997,
                            1998
                        ]
                    },
                },
                series: [
                    {
                        name: 'Cantidad de Productos',
                        data: [
                            30,
                            40,
                            35,
                            50,
                            49,
                            60,
                            70,
                            91
                        ]
                    }
                ],
                show_graphic: false,
            }
        },
        mounted() {
            EventBus.$on('show_graphic', function(response) {
                const data = response.data;
                const current_date_array = response.current_date.split('-');

                const current_date = `${current_date_array[2]}/${current_date_array[1]}/${current_date_array[0]}`;

                this.chartOptions.chart.title += ` desde ${current_date}`
                this.series[0].data = [];
                this.chartOptions.xaxis.categories = [];

                data.map(item => {
                    this.series[0].data.push(item.quantity);
                    this.chartOptions.xaxis.categories.push(item.expiration_date);
                });

                this.show_graphic = true;
                EventBus.$emit('loading', false);
            }.bind(this));
        }
    };
</script>