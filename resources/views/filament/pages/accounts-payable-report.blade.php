<x-filament-panels::page>
    {{-- Cabeçalho com Resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <x-heroicon-o-document-text class="w-5 h-5 text-white"/>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total de Contas
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $this->getViewData()['total_accounts'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <x-heroicon-o-currency-dollar class="w-5 h-5 text-white"/>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Valor Total
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                R$ {{ number_format($this->getViewData()['total_amount'], 2, ',', '.') }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <x-heroicon-o-clock class="w-5 h-5 text-white"/>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Pendentes
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $this->getViewData()['pending_count'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-white"/>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Em Atraso
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $this->getViewData()['overdue_count'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros Aplicados --}}
    @if($date_from || $date_to || $supplier_id || $status_filter)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mr-2"/>
                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Filtros Aplicados:</span>
            </div>
            <div class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                @if($date_from && $date_to)
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 text-xs font-medium mr-2">
                        📅 {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}
                    </span>
                @endif
                @if($supplier_id)
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 text-xs font-medium mr-2">
                        🏢 Fornecedor: {{ \App\Models\Supplier::find($supplier_id)?->name }}
                    </span>
                @endif
                @if($status_filter)
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 text-xs font-medium mr-2">
                        📊 Status: {{ $status_filter }}
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- Tabela do Relatório --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            {{ $this->table }}
        </div>
    </div>

    {{-- Rodapé com Informações --}}
    <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>Relatório gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Sistema Imagilab - Gestão Financeira</p>
    </div>
</x-filament-panels::page>