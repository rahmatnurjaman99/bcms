<script lang="ts">
    import type { PageData } from "./$types";
    import { goto } from "$app/navigation";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "$lib/components/ui/card";
    import { Input } from "$lib/components/ui/input";
    import { Button } from "$lib/components/ui/button";
    import { Badge } from "$lib/components/ui/badge";
    import Skeleton from "$lib/components/ui/skeleton.svelte";
    import DateRangePicker from "$lib/components/ui/date-range-picker.svelte";
    import { CalendarDate } from "@internationalized/date";
    import type { ActivityLog } from "$lib/types/activity";
    import ColumnVisibilityMenu from "$lib/components/ui/column-visibility-menu.svelte";
    import {
        buildDefaultVisibility,
        loadColumnVisibility,
        persistColumnVisibility,
        type ColumnVisibilityMap,
        type ColumnVisibilityOption,
    } from "$lib/utils/column-visibility";

    type ActivityFilters = {
        search: string | null;
        log: string | null;
        event: string | null;
        createdFrom: string | null;
        createdTo: string | null;
    };

    type DateRangeValue = {
        start: CalendarDate | undefined;
        end: CalendarDate | undefined;
    };

    function normalizeFilters(raw?: PageData["filters"]): ActivityFilters {
        return {
            search: raw?.search ?? null,
            log: raw?.log ?? null,
            event: raw?.event ?? null,
            createdFrom: raw?.createdFrom ?? null,
            createdTo: raw?.createdTo ?? null,
        };
    }

    function toCalendarDate(value: string | null) {
        if (!value) return undefined;
        const parsed = new Date(value);
        if (Number.isNaN(parsed.getTime())) return undefined;
        return new CalendarDate(
            parsed.getFullYear(),
            parsed.getMonth() + 1,
            parsed.getDate(),
        );
    }

    function rangeFromFilters(filters: ActivityFilters): DateRangeValue {
        return {
            start: toCalendarDate(filters.createdFrom),
            end: toCalendarDate(filters.createdTo),
        };
    }

    let { data } = $props<{ data: PageData }>();

    const initialFilters = normalizeFilters(data.filters);
    const initialDateRange = rangeFromFilters(initialFilters);
    const initialLoading = !data.activities && data.authorized && !data.error;

    let authorized = $state(data.authorized);
    let activities = $state<ActivityLog[]>(data.activities?.data ?? []);
    let paginator = $state(data.activities?.paginatorInfo ?? null);
    let filters = $state<ActivityFilters>(initialFilters);
    let loading = $state(initialLoading);
    let loadError = $state<string | null>(data.error ?? null);
    const eventOptions = ["created", "updated", "deleted", "activated"];
    let dateRange = $state<DateRangeValue>(initialDateRange);

    $effect(() => {
        const normalizedFilters = normalizeFilters(data.filters);
        authorized = data.authorized;
        activities = data.activities?.data ?? [];
        paginator = data.activities?.paginatorInfo ?? null;
        filters = normalizedFilters;
        loading = !data.activities && authorized && !data.error;
        loadError = data.error ?? null;
        dateRange = rangeFromFilters(normalizedFilters);
    });

    function resetFilters() {
        goto("/activity");
    }

    function eventVariant(
        event?: string | null,
    ): "secondary" | "default" | "outline" {
        if (!event) return "secondary";
        if (event === "created") return "default";
        if (event === "updated") return "secondary";
        if (event === "deleted") return "outline";
        return "secondary";
    }

    function formatDate(value: string): string {
        return new Intl.DateTimeFormat("en-US", {
            dateStyle: "medium",
            timeStyle: "short",
        }).format(new Date(value));
    }

    function calendarDateToValue(date: CalendarDate | undefined) {
        if (!date) return "";
        return new Date(date.year, date.month - 1, date.day)
            .toISOString()
            .slice(0, 10);
    }

    const queryString = (page: number) => {
        const params = new URLSearchParams();
        if (filters.search) params.set("search", filters.search);
        if (filters.log) params.set("log", filters.log);
        if (filters.event) params.set("event", filters.event);
        if (filters.createdFrom) params.set("createdFrom", filters.createdFrom);
        if (filters.createdTo) params.set("createdTo", filters.createdTo);
        if (page > 1) params.set("page", page.toString());
        return params.toString() ? `?${params.toString()}` : "";
    };

    const ACTIVITY_COLUMN_KEY = "activity-table-columns";
    const activityColumns: ColumnVisibilityOption[] = [
        { id: "when", label: "When" },
        { id: "event", label: "Event" },
        { id: "description", label: "Description" },
        { id: "subject", label: "Subject" },
        { id: "causer", label: "Causer" },
        { id: "log", label: "Log" },
    ];
    const activityColumnDefaults = buildDefaultVisibility(activityColumns);
    let activityColumnVisibility = $state<ColumnVisibilityMap>(
        loadColumnVisibility(ACTIVITY_COLUMN_KEY, activityColumns),
    );

    function toggleActivityColumn(columnId: string) {
        const current =
            activityColumnVisibility[columnId] ??
            activityColumnDefaults[columnId] ??
            true;
        activityColumnVisibility = {
            ...activityColumnVisibility,
            [columnId]: !current,
        };
    }

    function resetActivityColumns() {
        activityColumnVisibility = { ...activityColumnDefaults };
    }

    $effect(() => {
        persistColumnVisibility(
            ACTIVITY_COLUMN_KEY,
            activityColumnVisibility,
        );
    });

    const isActivityColumnVisible = (columnId: string) =>
        activityColumnVisibility[columnId] ??
        activityColumnDefaults[columnId] ??
        true;
</script>

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Activity</h1>
            <p class="text-sm text-muted-foreground">
                System-wide audit trail with filters by log, event, and text
                search.
            </p>
        </div>
    </div>

    {#if !authorized}
        <Card>
            <CardHeader>
                <CardTitle>No access</CardTitle>
            </CardHeader>
            <CardContent class="text-sm text-muted-foreground">
                You do not have permission to view activity logs.
            </CardContent>
        </Card>
    {:else}
        <Card>
            <CardHeader>
                <CardTitle>Activity log</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <form
                    class="space-y-3 rounded-lg border p-4"
                    method="get"
                    aria-label="Filter activity logs"
                >
                    <div class="grid gap-3 md:grid-cols-[2fr,1fr,1fr,1.5fr]">
                        <div class="space-y-1">
                            <label
                                class="text-xs font-medium text-muted-foreground"
                                for="search">Search description</label
                            >
                            <Input
                                id="search"
                                name="search"
                                placeholder="e.g., Activated academic year"
                                value={filters.search ?? ""}
                            />
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-xs font-medium text-muted-foreground"
                                for="log_name">Log name</label
                            >
                            <Input
                                id="log_name"
                                name="log"
                                placeholder="default, system..."
                                value={filters.log ?? ""}
                            />
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-xs font-medium text-muted-foreground"
                                for="event_select">Event</label
                            >
                            <select
                                id="event_select"
                                name="event"
                                class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                value={filters.event ?? ""}
                            >
                                <option value="">All events</option>
                                {#each eventOptions as option}
                                    <option
                                        value={option}
                                        selected={filters.event === option}
                                        >{option}</option
                                    >
                                {/each}
                            </select>
                        </div>
                        <DateRangePicker bind:value={dateRange} label="Created between" />
                        <input type="hidden" name="createdFrom" value={calendarDateToValue(dateRange.start)} />
                        <input type="hidden" name="createdTo" value={calendarDateToValue(dateRange.end)} />
                    </div>
                    <div class="flex flex-wrap justify-end gap-2">
                        <Button
                            type="button"
                            variant="ghost"
                            onclick={resetFilters}
                        >
                            Reset
                        </Button>
                        <Button type="submit">Apply</Button>
                    </div>
                </form>

                {#if loadError}
                    <div
                        class="rounded-lg border border-destructive/40 bg-destructive/10 p-3 text-sm text-destructive"
                    >
                        {loadError}
                    </div>
                {:else if loading}
                    <div class="space-y-3 rounded-lg border p-3">
                        {#each Array(6) as _, index (index)}
                            <div
                                class="grid grid-cols-[1.5fr,1fr,1.2fr,1fr,1fr] items-center gap-3"
                            >
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-6 w-20" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-full" />
                            </div>
                        {/each}
                    </div>
                {:else if !activities.length}
                    <p class="text-sm text-muted-foreground">
                        No activity found for this filter.
                    </p>
                {:else}
                    <div class="flex justify-end">
                        <ColumnVisibilityMenu
                            columns={activityColumns}
                            visibility={activityColumnVisibility}
                            toggleColumn={toggleActivityColumn}
                            resetColumns={resetActivityColumns}
                        />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[960px] text-left text-sm">
                            <thead
                                class="text-xs uppercase text-muted-foreground"
                            >
                                <tr>
                                    {#if isActivityColumnVisible("when")}
                                        <th class="pb-2">When</th>
                                    {/if}
                                    {#if isActivityColumnVisible("event")}
                                        <th class="pb-2">Event</th>
                                    {/if}
                                    {#if isActivityColumnVisible("description")}
                                        <th class="pb-2">Description</th>
                                    {/if}
                                    {#if isActivityColumnVisible("subject")}
                                        <th class="pb-2">Subject</th>
                                    {/if}
                                    {#if isActivityColumnVisible("causer")}
                                        <th class="pb-2">Causer</th>
                                    {/if}
                                    {#if isActivityColumnVisible("log")}
                                        <th class="pb-2">Log</th>
                                    {/if}
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                {#each activities as activity}
                                    <tr class="align-middle">
                                        {#if isActivityColumnVisible("when")}
                                            <td class="py-3 text-muted-foreground"
                                                >{formatDate(
                                                    activity.created_at,
                                                )}</td
                                            >
                                        {/if}
                                        {#if isActivityColumnVisible("event")}
                                            <td class="py-3">
                                                <Badge
                                                    variant={eventVariant(
                                                        activity.event,
                                                    )}
                                                >
                                                    {activity.event ?? "—"}
                                                </Badge>
                                            </td>
                                        {/if}
                                        {#if isActivityColumnVisible("description")}
                                            <td class="py-3">
                                                <div
                                                    class="font-medium text-foreground"
                                                >
                                                    {activity.description}
                                                </div>
                                                {#if activity.properties}
                                                    <details
                                                        class="mt-1 text-xs text-muted-foreground"
                                                    >
                                                        <summary
                                                            class="cursor-pointer select-none"
                                                            >Properties</summary
                                                        >
                                                        <pre
                                                            class="mt-1 max-h-40 overflow-auto rounded bg-muted/60 p-2">{JSON.stringify(
                                                                activity.properties,
                                                                null,
                                                                2,
                                                            )}</pre>
                                                    </details>
                                                {/if}
                                            </td>
                                        {/if}
                                        {#if isActivityColumnVisible("subject")}
                                            <td class="py-3 text-muted-foreground">
                                                {activity.subject_type
                                                    ? `${activity.subject_type} #${activity.subject_id ?? "?"}`
                                                    : "—"}
                                            </td>
                                        {/if}
                                        {#if isActivityColumnVisible("causer")}
                                            <td class="py-3 text-muted-foreground">
                                                {activity.causer_id
                                                    ? `${activity.causer_type ?? "User"} #${activity.causer_id}`
                                                    : "System"}
                                            </td>
                                        {/if}
                                        {#if isActivityColumnVisible("log")}
                                            <td class="py-3 text-muted-foreground"
                                                >{activity.log_name ??
                                                    "default"}</td
                                            >
                                        {/if}
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}

                {#if paginator && paginator.lastPage > 1}
                    <div
                        class="flex items-center justify-between text-sm text-muted-foreground"
                    >
                        <span
                            >Page {paginator.currentPage} of {paginator.lastPage}</span
                        >
                        <div class="flex gap-2">
                            {#if paginator.currentPage > 1}
                                <a
                                    class="rounded-md border px-3 py-1 hover:bg-accent hover:text-accent-foreground"
                                    href={queryString(
                                        paginator.currentPage - 1,
                                    )}
                                >
                                    Previous
                                </a>
                            {/if}
                            {#if paginator.currentPage < paginator.lastPage}
                                <a
                                    class="rounded-md border px-3 py-1 hover:bg-accent hover:text-accent-foreground"
                                    href={queryString(
                                        paginator.currentPage + 1,
                                    )}
                                >
                                    Next
                                </a>
                            {/if}
                        </div>
                    </div>
                {/if}
            </CardContent>
        </Card>
    {/if}
</div>
