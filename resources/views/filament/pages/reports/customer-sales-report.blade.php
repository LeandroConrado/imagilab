<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Resumo Geral --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="text-center">
                    <div class="text-2xl font-bold">
                        {{ \App\Models\Customer::count() }}
                    </div>
                    <div class="text-sm opacity-90">Total de Clientes</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                <div class="text-center">
                    <div class="text-2xl font-bold">
                        {{ \App\Models\Order::distinct('customer_id')->count() }}
                    </div>
                    <div class="text-sm opacity-90">Clientes Ativos</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                <div class="text-center">
                    <div class="text-2xl font-bold">
                        R$ {{ number_format(\App\Models\Order::avg('total') ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="text-sm opacity-90">Ticket Médio Geral</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Filtros Rápidos --}}
        <x-filament::section>
            <x-slot name="heading">
                🔍 Análise de Clientes
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="text-yellow-800 dark:text-yellow-200 text-sm font-medium">Novos Clientes (30 dias)</div>
                    <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                        {{ \App\Models\Customer::where('created_at', '>=', now()->subDays(30))->count() }}
                    </div>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="text-green-800 dark:text-green-200 text-sm font-medium">Clientes Premium (5+ pedidos)</div>
                    <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                        {{ \App\Models\Order::selectRaw('customer_id, COUNT(*) as orders')
                            ->groupBy('customer_id')
                            ->having('orders', '>=', 5)
                            ->count() }}
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="text-blue-800 dark:text-blue-200 text-sm font-medium">Pedido Mais Alto</div>
                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                        R$ {{ number_format(\App\Models\Order::max('total') ?? 0, 2, ',', '.') }}
                    </div>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
                    <div class="text-red-800 dark:text-red-200 text-sm font-medium">Sem Pedidos (90 dias)</div>
                    <div class="text-2xl font-bold text-red-900 dark:text-red-100">
                        {{ \App\Models\Customer::whereDoesntHave('orders', function($q) {
                            $q->where('created_at', '>=', now()->subDays(90));
                        })->count() }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Tabela Principal --}}
        <x-filament::section>
            <x-slot name="heading">
                📊 Ranking de Clientes
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

        {{-- Análise por Região --}}
        <x-filament::section>
            <x-slot name="heading">
                🗺️ Vendas por Região
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold mb-3">Top 5 Cidades</h4>
                    <div class="space-y-2">
                        @php
                            $cities = \App\Models\Customer::whereHas('orders')
                                ->get()
                                ->map(function($customer) {
                                    $addresses = is_string($customer->addresses)
                                        ? json_decode($customer->addresses, true) ?? []
                                        : $customer->addresses ?? [];

                                    foreach($addresses as $address) {
                                        if(isset($address['city'])) {
                                            return $address['city'];
                                        }
                                    }
                                    return 'Não informado';
                                })
                                ->countBy()
                                ->sortDesc()
                                ->take(5);
                        @endphp

                        @forelse($cities as $city => $count)
                            <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-800 rounded">
                                <span>{{ $city }}</span>
                                <span class="font-semibold">{{ $count }} clientes</span>
                            </div>
                        @empty
                            <div class="text-gray-500 text-center py-4">
                                Nenhum dado de região disponível
                            </div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold mb-3">Distribuição de Vendas</h4>
                    <div class="h-64">
                        <canvas id="regionChart"></canvas>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gráfico de Região (placeholder)
                const regionCtx = document.getElementById('regionChart');
                if (regionCtx) {
                    new Chart(regionCtx, {
                        type: 'pie',
                        data: {
                            labels: @json($cities->keys() ?? []),
                            datasets: [{
                                data: @json($cities->values() ?? []),
                                backgroundColor: [
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(245, 158, 11, 0.8)',
                                    'rgba(239, 68, 68, 0.8)',
                                    'rgba(139, 92, 246, 0.8)',
                                ]
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
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
