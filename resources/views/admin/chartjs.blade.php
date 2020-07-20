<canvas id="myChart" width="400" height="400"></canvas>
<script>
    $(function () {
        var ctx = document.getElementById("myChart").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['呼叫次数','跟进次数','放贷金额'],
                datasets: [{
                    label: '# of Votes',
                    data: [{{ $data }}],
                    backgroundColor: [],
                    borderColor: [],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
    });
</script>
