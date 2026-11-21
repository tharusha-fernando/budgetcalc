<x-filament-panels::page>
    <div class="mx-auto w-full max-w-6xl space-y-8">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="w-full lg:max-w-sm">
                    <label for="clearance-project" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Project
                    </label>
                    <select
                        id="clearance-project"
                        wire:model.live="project_id"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-950 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40 dark:border-white/10 dark:bg-gray-950 dark:text-white"
                    >
                        <option value="">Select a project</option>
                        @foreach (\App\Models\Project::query()->orderBy('name')->get() as $projectOption)
                            <option value="{{ $projectOption->id }}">{{ $projectOption->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($this->project)
                    <div class="text-sm text-gray-600 dark:text-gray-300 lg:pb-1 lg:text-right">
                        <div>{{ $this->project->vehicle ?: 'Vehicle not set' }}</div>
                        @if ($this->project->restoration_guy)
                            <div>{{ $this->project->restoration_guy }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($this->project)
            @php($summary = $this->summary)

            <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Settlement with guy
                </h2>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Agreed</div>
                        <div class="mt-1 text-lg font-semibold tabular-nums text-gray-950 dark:text-white sm:text-2xl">
                            {{ $summary['agreed'] !== null ? '₹'.number_format($summary['agreed'], 2) : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Paid</div>
                        <div class="mt-1 text-lg font-semibold tabular-nums text-sky-600 dark:text-sky-400 sm:text-2xl">
                            ₹{{ number_format($summary['paid_to_guy'], 2) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-amber-700 dark:text-amber-300">Still to pay</div>
                        <div class="mt-1 text-lg font-semibold tabular-nums text-amber-700 dark:text-amber-300 sm:text-2xl">
                            {{ $summary['still_owed_to_guy'] !== null ? '₹'.number_format($summary['still_owed_to_guy'], 2) : '—' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Purchases
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Gross</div>
                        <div class="mt-1 text-base font-semibold tabular-nums text-gray-950 dark:text-white sm:text-xl">
                            ₹{{ number_format($summary['gross'], 2) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Returned</div>
                        <div class="mt-1 text-base font-semibold tabular-nums text-amber-600 dark:text-amber-400 sm:text-xl">
                            ₹{{ number_format($summary['returned'], 2) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">I pay</div>
                        <div class="mt-1 text-base font-semibold tabular-nums text-gray-600 dark:text-gray-300 sm:text-xl">
                            ₹{{ number_format($summary['waived'], 2) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-emerald-700 dark:text-emerald-300">Charge guy</div>
                        <div class="mt-1 text-base font-semibold tabular-nums text-emerald-700 dark:text-emerald-300 sm:text-xl">
                            ₹{{ number_format($summary['chargeable'], 2) }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Bills
                </h2>

                <div class="space-y-4">
                    @forelse ($this->bills as $bill)
                        <article
                            wire:key="bill-{{ $bill->id }}"
                            class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900"
                        >
                            <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-3 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="truncate text-base font-semibold text-gray-950 dark:text-white">{{ $bill->vendor }}</div>
                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $bill->bill_date?->format('d M Y') }}
                                        @if ($bill->bill_number)
                                            · #{{ $bill->bill_number }}
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="rounded-md bg-emerald-50 px-2 py-1 font-medium text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                                            Charge ₹{{ number_format($bill->chargeableTotal(), 2) }}
                                        </span>
                                        <span class="rounded-md bg-gray-100 px-2 py-1 text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                            Gross ₹{{ number_format($bill->grossTotal(), 2) }}
                                        </span>
                                    </div>
                                    <x-filament::button
                                        tag="a"
                                        :href="$this->billUrl($bill)"
                                        color="gray"
                                        size="sm"
                                    >
                                        Open
                                    </x-filament::button>
                                    <x-filament::button
                                        size="sm"
                                        color="primary"
                                        wire:click="mountAction('addItem', { billId: {{ $bill->id }} })"
                                    >
                                        Add item
                                    </x-filament::button>
                                </div>
                            </div>

                            <div class="divide-y divide-gray-100 dark:divide-white/5">
                                @forelse ($bill->items as $item)
                                    <div wire:key="item-{{ $item->id }}" class="space-y-3 px-4 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="font-medium text-gray-950 dark:text-white">{{ $item->description }}</div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $item->category?->getLabel() }}
                                                    · {{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}
                                                    × ₹{{ number_format((float) $item->unit_price, 2) }}
                                                </div>
                                            </div>
                                            <div class="shrink-0 text-right text-sm font-semibold tabular-nums text-gray-950 dark:text-white">
                                                ₹{{ number_format((float) $item->line_total, 2) }}
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span @class([
                                                'inline-flex rounded-md px-2 py-0.5 text-xs font-medium',
                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' => $item->status === \App\Enums\BillItemStatus::Active,
                                                'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' => $item->status === \App\Enums\BillItemStatus::Returned,
                                                'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300' => $item->status === \App\Enums\BillItemStatus::Waived,
                                            ])>
                                                {{ $item->status?->getLabel() }}
                                            </span>

                                            <div class="flex flex-wrap gap-1">
                                                @if ($item->status === \App\Enums\BillItemStatus::Active)
                                                    <x-filament::button
                                                        size="xs"
                                                        color="warning"
                                                        wire:click="mountAction('markReturned', { itemId: {{ $item->id }} })"
                                                    >
                                                        Returned
                                                    </x-filament::button>
                                                @endif

                                                @if ($item->status !== \App\Enums\BillItemStatus::Waived)
                                                    <x-filament::button
                                                        size="xs"
                                                        color="gray"
                                                        wire:click="mountAction('markIPay', { itemId: {{ $item->id }} })"
                                                    >
                                                        I pay
                                                    </x-filament::button>
                                                @endif

                                                @if ($item->status !== \App\Enums\BillItemStatus::Active)
                                                    <x-filament::button
                                                        size="xs"
                                                        color="success"
                                                        wire:click="mountAction('markChargeGuy', { itemId: {{ $item->id }} })"
                                                    >
                                                        Charge
                                                    </x-filament::button>
                                                @endif

                                                <x-filament::button
                                                    size="xs"
                                                    color="danger"
                                                    wire:click="mountAction('deleteItem', { itemId: {{ $item->id }} })"
                                                >
                                                    Delete
                                                </x-filament::button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No items yet
                                    </div>
                                @endforelse
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/20 dark:text-gray-400">
                            No bills yet. Click <span class="font-medium text-gray-700 dark:text-gray-200">Add bill</span> above.
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-white/20 dark:text-gray-400">
                Create a project first, then come back to Clearance.
            </div>
        @endif
    </div>
</x-filament-panels::page>
