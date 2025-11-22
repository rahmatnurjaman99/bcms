<script lang="ts">
    import type { PageData } from "./$types";
    import { Button } from "$lib/components/ui/button";
    import { Badge } from "$lib/components/ui/badge";
    import { Plus, Pencil, Trash2, CheckCircle } from "@lucide/svelte";
    import {
        getAcademicYears,
        createAcademicYear,
        updateAcademicYear,
        deleteAcademicYear,
        setActiveAcademicYear,
    } from "$lib/api/academic-years";
    import { Input } from "$lib/components/ui/input";
    import { Label } from "$lib/components/ui/label";
    import { Textarea } from "$lib/components/ui/textarea";
    import { Checkbox } from "$lib/components/ui/checkbox";
    import { Calendar } from "$lib/components/ui/calendar";
    import { CalendarDate } from "@internationalized/date";
    import SlidePanel from "$lib/components/ui/slide-panel.svelte";
    import Skeleton from "$lib/components/ui/skeleton.svelte";
    import type {
        AcademicYear,
        CreateAcademicYearInput,
    } from "$lib/types/academic-year";
    import { toast } from "svelte-sonner";
    import { APP_NAME } from "$lib/config/app";
    import { browser } from "$app/environment";
    import ColumnVisibilityMenu from "$lib/components/ui/column-visibility-menu.svelte";
    import {
        buildDefaultVisibility,
        loadColumnVisibility,
        persistColumnVisibility,
        type ColumnVisibilityMap,
        type ColumnVisibilityOption,
    } from "$lib/utils/column-visibility";

    let { data } = $props<{ data: PageData }>();

    let academicYears: AcademicYear[] = $state(data.academicYears ?? []);
    let paginator = $state(data.paginator ?? null);
    let loading = $state(false);
    let error = $state(data.error ?? "");
    let isCreating = $state(false);
    let isUpdating = $state(false);
    let deletingId = $state<string | null>(null);
    let createPanelOpen = $state(false);
    let editPanelOpen = $state(false);
    let editingId = $state<string | null>(null);
    let createForm = $state<CreateAcademicYearInput>({
        name: "",
        start_date: "",
        end_date: "",
        is_active: false,
        description: "",
    });
    let createDate = $state<{
        start: CalendarDate | undefined;
        end: CalendarDate | undefined;
    }>({
        start: undefined,
        end: undefined,
    });
    let editForm = $state<CreateAcademicYearInput>({
        name: "",
        start_date: "",
        end_date: "",
        is_active: false,
        description: "",
    });
    let editDate = $state<{
        start: CalendarDate | undefined;
        end: CalendarDate | undefined;
    }>({
        start: undefined,
        end: undefined,
    });
    let createErrors = $state<Record<string, string>>({});
    let editErrors = $state<Record<string, string>>({});
    const minYear = 2020;
    const maxYear = new Date().getFullYear() + 1;
    const yearOptions = Array.from(
        { length: maxYear - minYear + 1 },
        (_, idx) => minYear + idx,
    );

    const SORTABLE_COLUMNS = [
        { id: "name", label: "Name" },
        { id: "start_date", label: "Start Date" },
        { id: "end_date", label: "End Date" },
        { id: "duration_in_days", label: "Duration" },
        { id: "is_active", label: "Status" },
        { id: "created_at", label: "Created" },
    ] as const;

    type SortField = (typeof SORTABLE_COLUMNS)[number]["id"];
    type SortDirection = "ASC" | "DESC";

    const DEFAULT_SORT_FIELD: SortField = "start_date";
    const DEFAULT_SORT_DIRECTION: SortDirection = "DESC";

    const initialSortField =
        SORTABLE_COLUMNS.find((column) => column.id === data.sortField)?.id ??
        DEFAULT_SORT_FIELD;
    let sortField = $state<SortField>(initialSortField);
    let sortDirection = $state<SortDirection>(
        data.sortDirection === "ASC" ? "ASC" : DEFAULT_SORT_DIRECTION,
    );

    const queryString = (page: number) => {
        const params = new URLSearchParams();
        if (page > 1) {
            params.set("page", page.toString());
        }
        params.set("sort", sortField);
        params.set("direction", sortDirection.toLowerCase());
        return params.toString() ? `?${params.toString()}` : "";
    };

    const ACADEMIC_YEAR_COLUMN_KEY = "academic-years-columns";
    const academicYearColumns: ColumnVisibilityOption[] = [
        { id: "name", label: "Name" },
        { id: "start", label: "Start Date" },
        { id: "end", label: "End Date" },
        { id: "duration", label: "Duration" },
        { id: "status", label: "Status" },
        { id: "actions", label: "Actions" },
    ];
    const academicYearColumnDefaults =
        buildDefaultVisibility(academicYearColumns);
    let academicYearColumnVisibility = $state<ColumnVisibilityMap>(
        loadColumnVisibility(ACADEMIC_YEAR_COLUMN_KEY, academicYearColumns),
    );

    function toggleAcademicYearColumn(columnId: string) {
        const current =
            academicYearColumnVisibility[columnId] ??
            academicYearColumnDefaults[columnId] ??
            true;
        academicYearColumnVisibility = {
            ...academicYearColumnVisibility,
            [columnId]: !current,
        };
    }

    function resetAcademicYearColumns() {
        academicYearColumnVisibility = { ...academicYearColumnDefaults };
    }

    $effect(() => {
        persistColumnVisibility(
            ACADEMIC_YEAR_COLUMN_KEY,
            academicYearColumnVisibility,
        );
    });

    const isAcademicYearColumnVisible = (columnId: string) =>
        academicYearColumnVisibility[columnId] ??
        academicYearColumnDefaults[columnId] ??
        true;

    const currentOrderBy = () => [
        {
            column: sortField,
            order: sortDirection,
        },
    ];

    function updateBrowserUrl(page: number) {
        if (!browser) return;
        const url = new URL(window.location.href);
        if (page > 1) {
            url.searchParams.set("page", page.toString());
        } else {
            url.searchParams.delete("page");
        }
        url.searchParams.set("sort", sortField);
        url.searchParams.set("direction", sortDirection.toLowerCase());
        const search = url.searchParams.toString();
        window.history.replaceState(
            {},
            "",
            `${url.pathname}${search ? `?${search}` : ""}`,
        );
    }

    async function loadAcademicYears(page?: number) {
        const targetPage = page ?? paginator?.currentPage ?? 1;
        try {
            loading = true;
            error = "";
            const response = await getAcademicYears({
                page: targetPage,
                orderBy: currentOrderBy(),
            });
            academicYears = response.data;
            paginator = response.paginatorInfo;
            updateBrowserUrl(targetPage);
        } catch (err) {
            error =
                err instanceof Error
                    ? err.message
                    : "Failed to load academic years";
            toast.error(error);
        } finally {
            loading = false;
        }
    }

    async function handleSortFieldChange(event: Event) {
        const target = event.target as HTMLSelectElement | null;
        if (!target) return;
        const nextField = SORTABLE_COLUMNS.find(
            (column) => column.id === target.value,
        )?.id;
        if (!nextField || nextField === sortField) {
            return;
        }
        sortField = nextField;
        sortDirection = DEFAULT_SORT_DIRECTION;
        await loadAcademicYears(1);
    }

    async function toggleSortDirection() {
        sortDirection = sortDirection === "ASC" ? "DESC" : "ASC";
        await loadAcademicYears(1);
    }

    function validate(form: CreateAcademicYearInput): Record<string, string> {
        const errors: Record<string, string> = {};

        if (!form.name?.trim()) {
            errors.name = "Name is required";
        }
        if (!form.start_date) {
            errors.start_date = "Start date is required";
        }
        if (!form.end_date) {
            errors.end_date = "End date is required";
        }
        if (
            form.start_date &&
            form.end_date &&
            form.start_date >= form.end_date
        ) {
            errors.end_date = "End date must be after start date";
        }

        return errors;
    }

    function resetCreateForm() {
        createForm = {
            name: "",
            start_date: "",
            end_date: "",
            is_active: false,
            description: "",
        };
        createErrors = {};
        createDate = { start: undefined, end: undefined };
    }

    function resetEditForm() {
        editForm = {
            name: "",
            start_date: "",
            end_date: "",
            is_active: false,
            description: "",
        };
        editErrors = {};
        editingId = null;
        editDate = { start: undefined, end: undefined };
    }

    function startCreate() {
        resetCreateForm();
        createPanelOpen = true;
    }

    function startEdit(year: AcademicYear) {
        editingId = year.id;
        const start = year.start_date ? new Date(year.start_date) : null;
        const end = year.end_date ? new Date(year.end_date) : null;
        editDate = {
            start: start
                ? new CalendarDate(
                      start.getFullYear(),
                      start.getMonth() + 1,
                      start.getDate(),
                  )
                : undefined,
            end: end
                ? new CalendarDate(
                      end.getFullYear(),
                      end.getMonth() + 1,
                      end.getDate(),
                  )
                : undefined,
        };
        editForm = {
            name: year.name,
            start_date: start ? start.toISOString().slice(0, 10) : "",
            end_date: end ? end.toISOString().slice(0, 10) : "",
            is_active: year.is_active,
            description: year.description ?? "",
        };
        editErrors = {};
        editPanelOpen = true;
    }

    async function handleCreate() {
        if (createDate.start) {
            const start = createDate.start;
            createForm.start_date = new Date(
                start.year,
                start.month - 1,
                start.day,
            )
                .toISOString()
                .slice(0, 10);
        }
        if (createDate.end) {
            const end = createDate.end;
            createForm.end_date = new Date(end.year, end.month - 1, end.day)
                .toISOString()
                .slice(0, 10);
        }

        const errors = validate(createForm);
        createErrors = errors;
        if (Object.keys(errors).length > 0) return;

        try {
            isCreating = true;
            const created = await createAcademicYear(createForm);
            academicYears = [created, ...academicYears];
            toast.success("Academic year created successfully");
            await loadAcademicYears(paginator?.currentPage ?? 1);
            createPanelOpen = false;
            resetCreateForm();
        } catch (err) {
            toast.error(
                err instanceof Error
                    ? err.message
                    : "Failed to create academic year",
            );
        } finally {
            isCreating = false;
        }
    }

    async function handleUpdate() {
        if (!editingId) return;

        if (editDate.start) {
            const start = editDate.start;
            editForm.start_date = new Date(
                start.year,
                start.month - 1,
                start.day,
            )
                .toISOString()
                .slice(0, 10);
        }
        if (editDate.end) {
            const end = editDate.end;
            editForm.end_date = new Date(end.year, end.month - 1, end.day)
                .toISOString()
                .slice(0, 10);
        }

        const errors = validate(editForm);
        editErrors = errors;
        if (Object.keys(errors).length > 0) return;

        try {
            isUpdating = true;
            const updated = await updateAcademicYear({
                id: editingId,
                ...editForm,
            });
            academicYears = academicYears.map((year) =>
                year.id === updated.id ? updated : year,
            );
            toast.success("Academic year updated successfully");
            editPanelOpen = false;
            await loadAcademicYears(paginator?.currentPage ?? 1);
            resetEditForm();
        } catch (err) {
            toast.error(
                err instanceof Error
                    ? err.message
                    : "Failed to update academic year",
            );
        } finally {
            isUpdating = false;
        }
    }

    async function handleSetActive(id: string) {
        try {
            await setActiveAcademicYear(id);
            toast.success("Academic year activated successfully");
            await loadAcademicYears();
        } catch (err) {
            toast.error(
                err instanceof Error
                    ? err.message
                    : "Failed to activate academic year",
            );
        }
    }

    async function handleDelete(id: string, name: string) {
        if (!confirm(`Are you sure you want to delete "${name}"?`)) {
            return;
        }

        try {
            deletingId = id;
            await deleteAcademicYear(id);
            toast.success("Academic year deleted successfully");
            await loadAcademicYears(paginator?.currentPage ?? 1);
        } catch (err) {
            toast.error(
                err instanceof Error
                    ? err.message
                    : "Failed to delete academic year",
            );
        } finally {
            deletingId = null;
        }
    }

    function formatDate(dateString: string): string {
        return new Date(dateString).toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
        });
    }
</script>

<svelte:head>
    <title>Academic Years | {APP_NAME}</title>
</svelte:head>

<div class="container mx-auto space-y-6 px-4 py-8">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">Academic Years</h1>
            <p class="mt-1 text-muted-foreground">
                Manage your school's academic years
            </p>
        </div>
        <Button onclick={startCreate}>
            <Plus class="mr-2 h-4 w-4" />
            New Academic Year
        </Button>
    </div>

    {#if loading}
        <div class="rounded-lg border bg-card p-4 shadow-sm">
            <div class="space-y-3">
                {#each Array(5) as _, index (index)}
                    <div
                        class="grid grid-cols-[1.6fr,1fr,1fr,1fr,1fr,1fr] items-center gap-3"
                    >
                        <Skeleton class="h-4 w-full" />
                        <Skeleton class="h-4 w-full" />
                        <Skeleton class="h-4 w-full" />
                        <Skeleton class="h-4 w-20" />
                        <Skeleton class="h-6 w-20" />
                        <div class="flex justify-end gap-2">
                            <Skeleton class="h-9 w-9" />
                            <Skeleton class="h-9 w-9" />
                            <Skeleton class="h-9 w-9" />
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    {:else if error}
        <div class="rounded-lg bg-destructive/10 px-4 py-3 text-destructive">
            {error}
        </div>
    {:else if academicYears.length === 0}
        <div class="rounded-lg border bg-card px-6 py-12 text-center shadow-sm">
            <p class="text-muted-foreground">
                No academic years found. Create one to get started.
            </p>
        </div>
    {:else}
        <div class="overflow-hidden rounded-lg border bg-card shadow-sm">
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-border bg-muted/40 px-4 py-2"
            >
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <label
                        for="sort_select"
                        class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                        >Sort by</label
                    >
                    <select
                        id="sort_select"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        value={sortField}
                        onchange={handleSortFieldChange}
                    >
                        {#each SORTABLE_COLUMNS as column}
                            <option value={column.id}>{column.label}</option>
                        {/each}
                    </select>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onclick={toggleSortDirection}
                        aria-pressed={sortDirection === "ASC"}
                    >
                        {sortDirection === "ASC" ? "Oldest first" : "Latest first"}
                    </Button>
                </div>
                <ColumnVisibilityMenu
                    columns={academicYearColumns}
                    visibility={academicYearColumnVisibility}
                    toggleColumn={toggleAcademicYearColumn}
                    resetColumns={resetAcademicYearColumns}
                />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-left">
                    <thead
                        class="bg-muted/50 text-xs font-medium uppercase tracking-wide text-muted-foreground"
                    >
                        <tr>
                            {#if isAcademicYearColumnVisible("name")}
                                <th class="px-6 py-3">Name</th>
                            {/if}
                            {#if isAcademicYearColumnVisible("start")}
                                <th class="px-6 py-3">Start Date</th>
                            {/if}
                            {#if isAcademicYearColumnVisible("end")}
                                <th class="px-6 py-3">End Date</th>
                            {/if}
                            {#if isAcademicYearColumnVisible("duration")}
                                <th class="px-6 py-3">Duration</th>
                            {/if}
                            {#if isAcademicYearColumnVisible("status")}
                                <th class="px-6 py-3">Status</th>
                            {/if}
                            {#if isAcademicYearColumnVisible("actions")}
                                <th class="px-6 py-3 text-right">Actions</th>
                            {/if}
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        {#each academicYears as year}
                            <tr class="transition-colors hover:bg-muted/50">
                                {#if isAcademicYearColumnVisible("name")}
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{year.name}</div>
                                        {#if year.description}
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                {year.description}
                                            </div>
                                        {/if}
                                    </td>
                                {/if}
                                {#if isAcademicYearColumnVisible("start")}
                                    <td class="px-6 py-4 text-muted-foreground"
                                        >{formatDate(year.start_date)}</td
                                    >
                                {/if}
                                {#if isAcademicYearColumnVisible("end")}
                                    <td class="px-6 py-4 text-muted-foreground"
                                        >{formatDate(year.end_date)}</td
                                    >
                                {/if}
                                {#if isAcademicYearColumnVisible("duration")}
                                    <td class="px-6 py-4 text-muted-foreground"
                                        >{year.duration_in_days} days</td
                                    >
                                {/if}
                                {#if isAcademicYearColumnVisible("status")}
                                    <td class="px-6 py-4">
                                        {#if year.is_active}
                                            <Badge variant="default">Active</Badge>
                                        {:else}
                                            <Badge variant="secondary"
                                                >Inactive</Badge
                                            >
                                        {/if}
                                    </td>
                                {/if}
                                {#if isAcademicYearColumnVisible("actions")}
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            {#if !year.is_active}
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onclick={() =>
                                                        handleSetActive(year.id)}
                                                    title="Set as active"
                                                >
                                                    <CheckCircle class="h-4 w-4" />
                                                </Button>
                                            {/if}
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() => startEdit(year)}
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() =>
                                                    handleDelete(
                                                        year.id,
                                                        year.name,
                                                    )}
                                                disabled={deletingId === year.id}
                                            >
                                                <Trash2
                                                    class="h-4 w-4 text-destructive"
                                                />
                                            </Button>
                                        </div>
                                    </td>
                                {/if}
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>
        {#if paginator && paginator.lastPage > 1}
            <div class="mt-4 flex items-center justify-between text-sm text-muted-foreground">
                <span>Page {paginator.currentPage} of {paginator.lastPage}</span>
                <div class="flex gap-2">
                    {#if paginator.currentPage > 1}
                        <a
                            class="rounded-md border px-3 py-1 hover:bg-accent hover:text-accent-foreground"
                            href={queryString(paginator.currentPage - 1)}
                        >
                            Previous
                        </a>
                    {/if}
                    {#if paginator.currentPage < paginator.lastPage}
                        <a
                            class="rounded-md border px-3 py-1 hover:bg-accent hover:text-accent-foreground"
                            href={queryString(paginator.currentPage + 1)}
                        >
                            Next
                        </a>
                    {/if}
                </div>
            </div>
        {/if}
    {/if}
</div>

<SlidePanel
    open={createPanelOpen}
    title="Create Academic Year"
    description="Add a new academic year to the system."
    onClose={() => {
        createPanelOpen = false;
        resetCreateForm();
    }}
>
    {#snippet children()}
        <div class="space-y-4">
            <div class="space-y-2">
                <Label for="name">Name *</Label>
                <Input
                    id="name"
                    type="text"
                    bind:value={createForm.name}
                    placeholder="e.g., 2024/2025"
                    class={createErrors.name ? "border-destructive" : ""}
                />
                {#if createErrors.name}
                    <p class="text-sm text-destructive">{createErrors.name}</p>
                {/if}
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="start_date">Start Date *</Label>
                    <Calendar
                        type="single"
                        bind:value={createDate.start}
                        captionLayout="dropdown"
                        years={yearOptions}
                        class={createErrors.start_date
                            ? "border-destructive rounded-md"
                            : ""}
                    />
                    {#if createErrors.start_date}
                        <p class="text-sm text-destructive">
                            {createErrors.start_date}
                        </p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label for="end_date">End Date *</Label>
                    <Calendar
                        type="single"
                        bind:value={createDate.end}
                        captionLayout="dropdown"
                        years={yearOptions}
                        class={createErrors.end_date
                            ? "border-destructive rounded-md"
                            : ""}
                    />
                    {#if createErrors.end_date}
                        <p class="text-sm text-destructive">
                            {createErrors.end_date}
                        </p>
                    {/if}
                </div>
            </div>
            <div class="space-y-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    bind:value={createForm.description}
                    placeholder="Optional description"
                    rows={3}
                />
            </div>
            <div class="flex items-center space-x-2">
                <Checkbox
                    id="create_is_active"
                    bind:checked={createForm.is_active}
                />
                <Label for="create_is_active" class="cursor-pointer"
                    >Set as active academic year</Label
                >
            </div>
        </div>
    {/snippet}
    {#snippet footer()}
        <div class="flex justify-end gap-2">
            <Button
                variant="ghost"
                onclick={() => (createPanelOpen = false)}
                disabled={isCreating}
            >
                Cancel
            </Button>
            <Button onclick={handleCreate} disabled={isCreating}>
                {isCreating ? "Creating..." : "Create"}
            </Button>
        </div>
    {/snippet}
</SlidePanel>

<SlidePanel
    open={editPanelOpen}
    title="Edit Academic Year"
    description="Update dates, description, or status."
    onClose={() => {
        editPanelOpen = false;
        resetEditForm();
    }}
>
    {#snippet children()}
        <div class="space-y-4">
            <div class="space-y-2">
                <Label for="edit_name">Name *</Label>
                <Input
                    id="edit_name"
                    type="text"
                    bind:value={editForm.name}
                    class={editErrors.name ? "border-destructive" : ""}
                />
                {#if editErrors.name}
                    <p class="text-sm text-destructive">{editErrors.name}</p>
                {/if}
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="edit_start_date">Start Date *</Label>
                    <Calendar
                        type="single"
                        bind:value={editDate.start}
                        captionLayout="dropdown"
                        years={yearOptions}
                        class={editErrors.start_date
                            ? "border-destructive rounded-md"
                            : ""}
                    />
                    {#if editErrors.start_date}
                        <p class="text-sm text-destructive">
                            {editErrors.start_date}
                        </p>
                    {/if}
                </div>
                <div class="space-y-2">
                    <Label for="edit_end_date">End Date *</Label>
                    <Calendar
                        type="single"
                        bind:value={editDate.end}
                        captionLayout="dropdown"
                        years={yearOptions}
                        class={editErrors.end_date
                            ? "border-destructive rounded-md"
                            : ""}
                    />
                    {#if editErrors.end_date}
                        <p class="text-sm text-destructive">
                            {editErrors.end_date}
                        </p>
                    {/if}
                </div>
            </div>
            <div class="space-y-2">
                <Label for="edit_description">Description</Label>
                <Textarea
                    id="edit_description"
                    bind:value={editForm.description}
                    rows={3}
                />
            </div>
            <div class="flex items-center space-x-2">
                <Checkbox
                    id="edit_is_active"
                    bind:checked={editForm.is_active}
                />
                <Label for="edit_is_active" class="cursor-pointer"
                    >Set as active academic year</Label
                >
            </div>
        </div>
    {/snippet}
    {#snippet footer()}
        <div class="flex justify-end gap-2">
            <Button
                variant="ghost"
                onclick={() => (editPanelOpen = false)}
                disabled={isUpdating}
            >
                Cancel
            </Button>
            <Button onclick={handleUpdate} disabled={isUpdating || !editingId}>
                {isUpdating ? "Saving..." : "Save changes"}
            </Button>
        </div>
    {/snippet}
</SlidePanel>
