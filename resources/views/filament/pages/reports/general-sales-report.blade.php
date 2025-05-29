<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Formulário de Filtros --}}
        <x-filament::section>
            <x-slot name="heading">
                Filtros do Relatório
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        {{-- Métricas Principais --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::section class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="text-center">
                    <div class="text-2xl font-bold">
                        R$ {{ number_format($data['metrics']['total_sales'] ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="text-sm opacity-90">Total de Vendas</div>
                    @if(($data['metrics']['sales_growth'] ?? 0) != 0)
                        <div class="text-xs mt-1">
                            @if($data['metrics']['sales_growth'] > 0)
                                <span class="text-green-200">↗ {{ number_format($data['metrics']['sales_growth'], 1) }}%</span>
                            @else
                                <span class="text-red-200">↘ {{ number_format(abs($data['metrics']['sales_growth']), 1) }}%</span>
                            @endif
                        </div>
                    @endif
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $data['metrics']['total_orders'] ?? 0 }}</div>
                    <div class="text-sm opacity-90">Total de Pedidos</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                <div class="text-center">
                    <div class="text-2xl font-bold">
                        R$ {{ number_format($data['metrics']['average_ticket'] ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="text-sm opacity-90">Ticket Médio</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $data['metrics']['delivered_orders'] ?? 0 }}</div>
                    <div class="text-sm opacity-90">Pedidos Entregues</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Gráficos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Vendas por Período --}}
            <x-filament::section>
                <x-slot name="heading">
                    📈 Vendas por Período
                </x-slot>

                <div class="h-80">
                    <canvas id="salesChart"></canvas>
                </div>
            </x-filament::section>

            {{-- Status dos Pedidos --}}
            <x-filament::section>
                <x-slot name="heading">
                    📊 Status dos Pedidos
                </x-slot>

                <div class="h-80">
                    <canvas id="statusChart"></canvas>
                </div>
            </x-filament::section>
        </div>

        {{-- Tabelas de Top --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top Produtos --}}
            <x-filament::section>
                <x-slot name="heading">
                    🏆 Top 10 Produtos
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left">Produto</th>
                            <th class="px-4 py-2 text-right">Qtd</th>
                            <th class="px-4 py-2 text-right">Vendas</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['top_products'] ?? [] as $product)
                            <tr>
                                <td class="px-4 py-2">{{ $product['product_name'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $product['quantity'] }}</td>
                                <td class="px-4 py-2 text-right">R$ {{ number_format($product['sales'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-center text-gray-500">
                                    Nenhum produto encontrado no período
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- Top Clientes --}}
            <x-filament::section>
                <x-slot name="heading">
                    👑 Top 10 Clientes
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left">Cliente</th>
                            <th class="px-4 py-2 text-right">Pedidos</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['top_customers'] ?? [] as $customer)
                            <tr>
                                <td class="px-4 py-2">{{ $customer['customer_name'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $customer['total_orders'] }}</td>
                                <td class="px-4 py-2 text-right">R$ {{ number_format($customer['total_sales'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-center text-gray-500">
                                    Nenhum cliente encontrado no período
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </div>

    {{-- Scripts dos Gráficos --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gráfico de Vendas por Período
                const salesCtx = document.getElementById('salesChart').getContext('2d');
                const salesData = @json($data['sales_by_period'] ?? []);

                new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: salesData.map(item => item.date),
                        datasets: [{
                            label: 'Vendas (R$)',
                            data: salesData.map(item => item.total),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR');
                                    }
                                }
                            }
                        }
                    }
                });

                // Gráfico de Status dos Pedidos
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                const statusData = @json($data['order_status'] ?? []);

                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.map(item => item.status),
                        datasets: [{
                            data: statusData.map(item => item.count),
                            backgroundColor: [
                                'rgba(245, 158, 11, 0.8)',  // Pendente
                                'rgba(59, 130, 246, 0.8)',  // Processando
                                'rgba(139, 92, 246, 0.8)',  // Enviado
                                'rgba(34, 197, 94, 0.8)',   // Entregue
                                'rgba(239, 68, 68, 0.8)',   // Cancelado
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
