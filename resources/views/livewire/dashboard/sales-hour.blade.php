<div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 mt-4">
    <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Ventas por hora (6 a.m. a 10 p.m.)</h4>
    <canvas id="ventasPorHoraChart"></canvas>

    <script>
        document.addEventListener('livewire:navigated', function () {
            const ctx = document.getElementById('ventasPorHoraChart').getContext('2d');
            const isDark = document.documentElement.classList.contains('dark');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! $labelsJson !!},
                    datasets: [{
                        label: 'Ventas',
                        data: {!! $ventasPorHoraJson !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: isDark ? '#ffffff' : '#000000'
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: isDark ? '#ffffff' : '#000000'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: isDark ? '#ffffff' : '#000000'
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>
