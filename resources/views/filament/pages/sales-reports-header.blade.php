<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold mb-2">📊 Relatórios de Vendas</h1>
            <p class="text-blue-100">Análise completa do desempenho de vendas da sua empresa</p>
        </div>
        <div class="text-right">
            <div class="text-2xl font-bold">
                R$ {{ number_format(\App\Models\Order::sum('total'), 2, ',', '.') }}
            </div>
            <div class="text-sm text-blue-100">Total Geral em Vendas</div>
        </div>
    </div>
</div>

{{-- Menu de Navegação dos Relatórios --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <a href="{{ \App\Filament\Resources\SalesReportResource::getUrl('general') }}"
       class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="text-3xl mr-4">📊</div>
            <div>
                <h3 class="font-semibold text-lg">Relatório Geral</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Visão geral das vendas, métricas e performance</p>
                <p class="text-blue-600 text-xs mt-1">{{ \App\Models\Order::count() }} pedidos no total</p>
            </div>
        </div>
    </a>

    <a href="{{ \App\Filament\Resources\SalesReportResource::getUrl('customer') }}"
       class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="text-3xl mr-4">👥</div>
            <div>
                <h3 class="font-semibold text-lg">Por Cliente</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Ranking e análise detalhada por cliente</p>
                <p class="text-green-600 text-xs mt-1">{{ \App\Models\Customer::count() }} clientes cadastrados</p>
            </div>
        </div>
    </a>

    <a href="{{ \App\Filament\Resources\SalesReportResource::getUrl('product') }}"
       class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-purple-500">
        <div class="flex items-center">
            <div class="text-3xl mr-4">📦</div>
            <div>
                <h3 class="font-semibold text-lg">Por Produto</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Performance de produtos e análise de estoque</p>
                <p class="text-purple-600 text-xs mt-1">{{ \App\Models\Product::count() }} produtos no catálogo</p>
            </div>
        </div>
    </a>

    <a href="{{ \App\Filament\Resources\SalesReportResource::getUrl('category') }}"
       class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🏷️</div>
            <div>
                <h3 class="font-semibold text-lg">Por Categoria</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Análise de vendas por categoria de produto</p>
                <p class="text-yellow-600 text-xs mt-1">{{ \App\Models\Category::count() }} categorias ativas</p>
            </div>
        </div>
    </a>

    <a href="{{ \App\Filament\Resources\SalesReportResource::getUrl('supplier') }}"
       class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🚚</div>
            <div>
                <h3 class="font-semibold text-lg">Por Fornecedor</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Performance de produtos por fornecedor</p>
                <p class="text-red-600 text-xs mt-1">{{ \App\Models\Supplier::count() }} fornecedores cadastrados</p>
            </div>
        </div>
    </a>

    <a href="{{ \App\Filament\Resources\SalesReportResource::getUrl('region') }}"
       class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-indigo-500">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🗺️</div>
            <div>
                <h3 class="font-semibold text-lg">Por Região</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Distribuição geográfica das vendas</p>
                <p class="text-indigo-600 text-xs mt-1">Análise por cidade e estado</p>
            </div>
        </div>
    </a>
</div>

{{-- Resumo Rápido --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg">
        <div class="text-center">
            <div class="text-2xl font-bold">{{ \App\Models\Order::whereDate('created_at', today())->count() }}</div>
            <div class="text-sm opacity-90">Pedidos Hoje</div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg">
        <div class="text-center">
            <div class="text-2xl font-bold">{{ \App\Models\Order::where('status', 'pending')->count() }}</div>
            <div class="text-sm opacity-90">Pedidos Pendentes</div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg">
        <div class="text-center">
            <div class="text-2xl font-bold">
                R$ {{ number_format(\App\Models\Order::whereMonth('created_at', now()->month)->sum('total'), 0, ',', '.') }}
            </div>
            <div class="text-sm opacity-90">Vendas do Mês</div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-4 rounded-lg">
        <div class="text-center">
            <div class="text-2xl font-bold">
                R$ {{ number_format(\App\Models\Order::avg('total') ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-sm opacity-90">Ticket Médio</div>
        </div>
    </div>
</div>
