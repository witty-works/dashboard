<x-jet-label for="chart" value="chart type" />

<x-select
    :options="\App\Http\Livewire\OrganizationAnalytics::CHART_TYPES"
    wire:model.defer="chart"
/>

<x-jet-input-error for="chart" class="mt-2" />

<button wire:click="updateChartData">update chart</button>

<div x-data="{ chart: @js($chart), chartData: @js($chartData) }">
    the chart ..
</div>
