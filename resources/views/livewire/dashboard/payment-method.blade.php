<div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 mt-4">
    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Métodos de Pago</h3>
    <canvas id="metodosPagoChart"></canvas>

    <script>
        document.addEventListener('livewire:navigated', function () {
            const ctx = document.getElementById('metodosPagoChart').getContext('2d');
            const montos = {!! $montosJson !!};

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! $labelsJson !!},
                    datasets: [{
                        label: 'Métodos de Pago',
                        data: {!! $valoresJson !!},
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
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
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    // Retornamos un array con la línea del label y luego una línea vacía para separación
                                    return [` ${label}: ${value} ventas`, ''];
                                },
                                afterLabel: function(context) {
                                    const monto = montos[context.dataIndex];
                                    return ` Total: S/ ${monto.toLocaleString('es-PE', { minimumFractionDigits: 2 })}`;
                                }
                            }
                        }

                    }
                }
            });
        });
    </script>
</div>
