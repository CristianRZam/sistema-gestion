<div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 mt-4">
    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Top 10 Productos Más Vendidos</h3>
    <canvas id="topProductosChart"></canvas>

    <script>
        window.addEventListener('actualizarGraficoTopProductos', (event) => {
            const data = event.detail[0];
            const { labels, data: cantidades } = data;

            const ctx = document.getElementById('topProductosChart')?.getContext('2d');
            if (!ctx) return;

            // ✅ Verifica si existe y es instancia de Chart
            if (window.topProductosChart instanceof Chart) {
                window.topProductosChart.destroy();
            }

            // ✅ Crea el nuevo gráfico
            window.topProductosChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Productos más vendidos',
                        data: cantidades,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(199, 199, 199, 0.7)',
                            'rgba(83, 102, 255, 0.7)',
                            'rgba(255, 99, 71, 0.7)',
                            'rgba(0, 255, 127, 0.7)'
                        ],
                        borderColor: 'rgba(255, 255, 255, 0.8)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#000000'
                            }
                        }
                    }
                }
            });
        });
    </script>

</div>
