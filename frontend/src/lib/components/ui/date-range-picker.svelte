<script lang="ts">
    import {
        CalendarDate,
        getLocalTimeZone,
        today,
    } from "@internationalized/date";
    import { Calendar } from "$lib/components/ui/calendar";
    import {
        Popover,
        PopoverContent,
        PopoverTrigger,
    } from "$lib/components/ui/popover";
    import { Button, buttonVariants } from "$lib/components/ui/button";
    import { cn } from "$lib/utils";

    type Range = {
        start: CalendarDate | undefined;
        end: CalendarDate | undefined;
    };

    let {
        label = "Date range",
        value = $bindable<Range>({ start: undefined, end: undefined }),
        class: className,
        id = "date-range",
        showPresets = true,
        presets = defaultPresets(),
        children,
        ...restProps
    } = $props<{
        label?: string;
        value?: Range;
        class?: string;
        id?: string;
        showPresets?: boolean;
        presets?: { label: string; start: CalendarDate; end: CalendarDate }[];
    }>();

    function defaultPresets() {
        const todayDate = today(getLocalTimeZone());
        return [
            { label: "Today", start: todayDate, end: todayDate },
            {
                label: "Last 7 days",
                start: todayDate.subtract({ days: 6 }),
                end: todayDate,
            },
            {
                label: "Last 30 days",
                start: todayDate.subtract({ days: 29 }),
                end: todayDate,
            },
        ];
    }

    function applyPreset(range: { start: CalendarDate; end: CalendarDate }) {
        value = { start: range.start, end: range.end };
    }

    function formatRange(range: Range) {
        if (!range.start || !range.end) return "Pick a date range";
        const toDate = (date: CalendarDate) =>
            new Date(date.year, date.month - 1, date.day).toLocaleDateString(
                "id-ID",
                {
                    month: "short",
                    day: "numeric",
                    year: "numeric",
                },
            );
        return `${toDate(range.start)} - ${toDate(range.end)}`;
    }

    let startCalendarValue = $state(value.start);
    let endCalendarValue = $state(value.end);

    $effect(() => {
        value = {
            start: startCalendarValue,
            end: endCalendarValue,
        };
    });

    $effect(() => {
        if (value.start !== startCalendarValue) {
            startCalendarValue = value.start;
        }
        if (value.end !== endCalendarValue) {
            endCalendarValue = value.end;
        }
    });
</script>

<div class={cn("space-y-1", className)}>
    <label for={id} class="text-xs font-medium text-muted-foreground"
        >{label}</label
    >
    <Popover {...restProps}>
        <PopoverTrigger>
            <button
                {id}
                type="button"
                class={cn(
                    buttonVariants({ variant: "outline" }),
                    "w-full justify-between text-left font-normal",
                )}
            >
                {formatRange(value)}
            </button>
        </PopoverTrigger>
		<PopoverContent class="w-auto rounded-2xl border bg-card p-4 shadow-xl" align="start">
            <div class="flex flex-col gap-4 md:flex-row">
                {#if showPresets}
                    <div class="max-w-[200px] space-y-2">
                        <p class="text-sm font-semibold text-foreground">
                            Quick ranges
                        </p>
                        <div class="space-y-2">
                            {#each presets as preset}
                                <Button
                                    type="button"
                                    variant="ghost"
                                    class="w-full justify-start text-sm"
                                    onclick={() => applyPreset(preset)}
                                >
                                    {preset.label}
                                </Button>
                            {/each}
                        </div>
                    </div>
                {/if}
                <div class="flex flex-col gap-3 md:flex-row">
                    <div class="rounded-md border p-3">
                        <p
                            class="mb-2 text-xs font-semibold text-muted-foreground"
                        >
                            Start date
                        </p>
						<Calendar
							type="single"
							bind:value={startCalendarValue}
							captionLayout="dropdown"
							class="border-none bg-card rounded-md"
							weekdayFormat="short"
						/>
                    </div>
                    <div class="rounded-md border p-3">
                        <p
                            class="mb-2 text-xs font-semibold text-muted-foreground"
                        >
                            End date
                        </p>
						<Calendar
							type="single"
							bind:value={endCalendarValue}
							captionLayout="dropdown"
							class="border-none bg-card rounded-md"
							weekdayFormat="short"
						/>
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</div>
