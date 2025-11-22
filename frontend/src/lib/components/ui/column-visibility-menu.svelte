<script lang="ts">
    import SlidersHorizontal from "@lucide/svelte/icons/sliders-horizontal";
    import {
        DropdownMenu,
        DropdownMenuCheckboxItem,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from "$lib/components/ui/dropdown-menu";
    import { cn } from "$lib/utils.js";
    import type {
        ColumnVisibilityMap,
        ColumnVisibilityOption,
    } from "$lib/utils/column-visibility";

    const {
        columns,
        visibility,
        toggleColumn,
        resetColumns,
        buttonLabel = "Columns",
        class: className,
    }: {
        columns: ColumnVisibilityOption[];
        visibility: ColumnVisibilityMap;
        toggleColumn: (id: string) => void;
        resetColumns?: () => void;
        buttonLabel?: string;
        class?: string;
    } = $props();

    const showReset = $derived(typeof resetColumns === "function");

    function handleToggle(id: string) {
        const column = columns.find((entry) => entry.id === id);
        if (!column || column.alwaysVisible) return;
        toggleColumn(id);
    }

    function handleReset(event: Event) {
        event.preventDefault();
        resetColumns?.();
    }
</script>

<DropdownMenu>
    <DropdownMenuTrigger
        type="button"
        class={cn(
            "inline-flex h-9 items-center gap-2 rounded-md border border-input bg-background px-3 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
            className,
        )}
        aria-label="Toggle table columns"
    >
        <SlidersHorizontal class="h-3.5 w-3.5" />
        {buttonLabel}
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-56">
        <DropdownMenuLabel>Select columns</DropdownMenuLabel>
        <DropdownMenuSeparator />
        {#each columns as column (column.id)}
            <DropdownMenuCheckboxItem
                checked={visibility[column.id] ?? true}
                disabled={column.alwaysVisible}
                onCheckedChange={() => handleToggle(column.id)}
            >
                <span class="flex w-full items-center justify-between gap-2">
                    <span>{column.label}</span>
                    {#if column.alwaysVisible}
                        <span class="text-[10px] uppercase tracking-wide text-muted-foreground"
                            >Required</span
                        >
                    {/if}
                </span>
            </DropdownMenuCheckboxItem>
        {/each}
        {#if showReset}
            <DropdownMenuSeparator />
            <DropdownMenuItem
                class="text-destructive focus:text-destructive"
                onSelect={handleReset}
            >
                Reset defaults
            </DropdownMenuItem>
        {/if}
    </DropdownMenuContent>
</DropdownMenu>
