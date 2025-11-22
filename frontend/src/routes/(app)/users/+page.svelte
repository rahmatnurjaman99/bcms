<script lang="ts">
    import type { PageData } from "./$types";
    import type { Permission, Role } from "$lib/types/user";
	import {
		Card,
		CardContent,
		CardDescription,
		CardHeader,
		CardTitle,
	} from "$lib/components/ui/card";
	import { Avatar, AvatarFallback, AvatarImage } from "$lib/components/ui/avatar";
    import { Input } from "$lib/components/ui/input";
    import { Button } from "$lib/components/ui/button";
    import Skeleton from "$lib/components/ui/skeleton.svelte";
    import SlidePanel from "$lib/components/ui/slide-panel.svelte";
    import { APP_NAME } from "$lib/config/app";
    import { browser } from "$app/environment";
    import { toast } from "svelte-sonner";
    import { createUser, deleteUser, updateUser, type UserListItem } from "$lib/api/admin";
    import { onMount } from "svelte";
    import ColumnVisibilityMenu from "$lib/components/ui/column-visibility-menu.svelte";
    import {
        buildDefaultVisibility,
        loadColumnVisibility,
        persistColumnVisibility,
        type ColumnVisibilityMap,
        type ColumnVisibilityOption,
    } from "$lib/utils/column-visibility";

    let { data } = $props<{ data: PageData }>();

    const authorized = data.authorized;
    const canManageUsers = data.canManageUsers;
    const canManageRoles = data.canManageRoles;
    let users = $state(data.users?.data ?? []);
    let paginator = $state(data.users?.paginatorInfo);
    let roles = $state(data.roles ?? []);

    const searchValue = data.search ?? "";

    const formatRoles = (rolesList: Role[]) =>
        rolesList.map((role: Role) => role.name).join(", ") || "—";
    const formatPermissions = (permissionsList: Permission[]) =>
        permissionsList.map((perm: Permission) => perm.name).join(", ") ||
        "—";
    const initials = (fullName: string) =>
        fullName
            .split(/\s+/)
            .map((part) => part?.[0] ?? "")
            .filter(Boolean)
            .slice(0, 2)
            .join("")
            .toUpperCase();

    const queryString = (page: number) => {
        const params = new URLSearchParams();
        if (searchValue) params.set("search", searchValue);
        if (page > 1) params.set("page", page.toString());
        return params.toString() ? `?${params.toString()}` : "";
    };

    const USER_COLUMN_KEY = "users-table-columns";
    const baseUserColumns: ColumnVisibilityOption[] = [
        { id: "avatar", label: "Avatar" },
        { id: "name", label: "Name" },
        { id: "email", label: "Email" },
        { id: "status", label: "Status" },
        { id: "roles", label: "Roles" },
        { id: "permissions", label: "Permissions" },
    ];
    const userColumns: ColumnVisibilityOption[] = canManageUsers
        ? [...baseUserColumns, { id: "actions", label: "Actions" }]
        : baseUserColumns;
    const userColumnDefaults = buildDefaultVisibility(userColumns);
    let userColumnVisibility = $state<ColumnVisibilityMap>(
        loadColumnVisibility(USER_COLUMN_KEY, userColumns),
    );

    function toggleUserColumn(columnId: string) {
        const current =
            userColumnVisibility[columnId] ??
            userColumnDefaults[columnId] ??
            true;
        userColumnVisibility = {
            ...userColumnVisibility,
            [columnId]: !current,
        };
    }

    function resetUserColumns() {
        userColumnVisibility = { ...userColumnDefaults };
    }

    $effect(() => {
        persistColumnVisibility(USER_COLUMN_KEY, userColumnVisibility);
    });

    const isUserColumnVisible = (columnId: string) =>
        userColumnVisibility[columnId] ??
        userColumnDefaults[columnId] ??
        true;

    let createForm = $state({
        name: "",
        email: "",
        password: "",
        passwordConfirmation: "",
        status: true,
        roles: [] as string[],
    });
    let isCreatingUser = $state(false);
    let createPanelOpen = $state(false);
    let editPanelOpen = $state(false);

    let editingUserId = $state<string | null>(null);
    let editForm = $state({
        name: "",
        email: "",
        status: true,
        password: "",
        roles: [] as string[],
    });
    let isUpdatingUser = $state(false);
    let isDeletingId = $state<string | null>(null);
    let loadingDirectory = $state(browser && authorized);

    onMount(() => {
        loadingDirectory = false;
    });

    function toggleRole(roleName: string, target: { roles: string[] }) {
        target.roles = target.roles.includes(roleName)
            ? target.roles.filter((entry) => entry !== roleName)
            : [...target.roles, roleName];
    }

    function startEditUser(user: UserListItem) {
        editingUserId = user.id;
        editForm = {
            name: user.name,
            email: user.email,
            status: user.status,
            password: "",
            roles: user.roles.map((role: Role) => role.name),
        };
        editPanelOpen = true;
    }

    function resetCreateForm() {
        createForm = {
            name: "",
            email: "",
            password: "",
            passwordConfirmation: "",
            status: true,
            roles: [],
        };
    }

    function resetEditForm() {
        editForm = {
            name: "",
            email: "",
            status: true,
            password: "",
            roles: [],
        };
    }

    function closeCreatePanel() {
        createPanelOpen = false;
        resetCreateForm();
    }

    function closeEditPanel() {
        editPanelOpen = false;
        editingUserId = null;
        resetEditForm();
    }

    async function handleCreateUser() {
        isCreatingUser = true;
        try {
            const newUser = await createUser({
                ...createForm,
                roles: canManageRoles ? createForm.roles : undefined,
                fetchImpl: fetch,
            });
            users = [newUser, ...users];
            toast.success("User created");
            closeCreatePanel();
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Unable to create user",
            );
        } finally {
            isCreatingUser = false;
        }
    }

    async function handleUpdateUser() {
        if (!editingUserId) return;
        isUpdatingUser = true;
        try {
            const updated = await updateUser({
                id: editingUserId,
                name: editForm.name,
                email: editForm.email,
                status: editForm.status,
                password: editForm.password || undefined,
                roles: canManageRoles ? editForm.roles : undefined,
                fetchImpl: fetch,
            });
            users = users.map((user: UserListItem) =>
                user.id === updated.id ? updated : user,
            );
            toast.success("User updated");
            closeEditPanel();
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Unable to update user",
            );
        } finally {
            isUpdatingUser = false;
        }
    }

    async function handleDeleteUser(id: string) {
        if (!confirm("Delete this user?")) return;
        isDeletingId = id;
        try {
            const deleted = await deleteUser({ id, fetchImpl: fetch });
            if (deleted) {
                users = users.filter((user: UserListItem) => user.id !== id);
                toast.success("User deleted");
            }
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Unable to delete user",
            );
        } finally {
            isDeletingId = null;
        }
    }
</script>

<svelte:head>
    <title>Users | {APP_NAME}</title>
</svelte:head>

<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Users</h1>
            <p class="text-sm text-muted-foreground">
                Manage team members and their roles.
            </p>
        </div>
        {#if authorized && canManageUsers}
            <Button onclick={() => (createPanelOpen = true)}>New user</Button>
        {/if}
    </div>

    {#if !authorized}
        <Card>
            <CardHeader>
                <CardTitle>No access</CardTitle>
                <CardDescription
                    >You do not have permission to view users.</CardDescription
                >
            </CardHeader>
        </Card>
    {:else}
        <Card>
            <CardHeader
                class="gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <CardTitle>Directory</CardTitle>
                    <CardDescription>
                        {paginator?.total ?? users.length} members synced from {APP_NAME}.
                    </CardDescription>
                </div>
                <form class="flex w-full gap-2 md:w-auto" method="get">
                    <Input
                        name="search"
                        placeholder="Search by name or email"
                        value={searchValue}
                        aria-label="Search users"
                    />
                    <Button type="submit">Filter</Button>
                </form>
            </CardHeader>
            <CardContent class="space-y-4">
                {#if loadingDirectory}
                    <div class="space-y-3 rounded-lg border p-3">
                        {#each Array(5) as _, index (index)}
                            <div
                                class="grid grid-cols-[2fr,2fr,1fr,1.5fr,1.5fr] items-center gap-3"
                            >
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-16" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-full" />
                            </div>
                        {/each}
                    </div>
                {/if}

                {#if users.length === 0 && !loadingDirectory}
                    <p class="text-sm text-muted-foreground">No users found.</p>
                {:else}
                    <div class="space-y-2">
                        <div class="flex justify-end">
                            <ColumnVisibilityMenu
                                columns={userColumns}
                                visibility={userColumnVisibility}
                                toggleColumn={toggleUserColumn}
                                resetColumns={resetUserColumns}
                            />
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] text-left text-sm">
                                <thead
                                    class="text-xs uppercase text-muted-foreground"
                                >
                                    <tr>
                                        {#if isUserColumnVisible("avatar")}
                                            <th class="pb-2">Avatar</th>
                                        {/if}
                                        {#if isUserColumnVisible("name")}
                                            <th class="pb-2">Name</th>
                                        {/if}
                                        {#if isUserColumnVisible("email")}
                                            <th class="pb-2">Email</th>
                                        {/if}
                                        {#if isUserColumnVisible("status")}
                                            <th class="pb-2">Status</th>
                                        {/if}
                                        {#if isUserColumnVisible("roles")}
                                            <th class="pb-2">Roles</th>
                                        {/if}
                                        {#if isUserColumnVisible("permissions")}
                                            <th class="pb-2">Permissions</th>
                                        {/if}
                                        {#if canManageUsers && isUserColumnVisible("actions")}
                                            <th class="pb-2 text-right">Actions</th>
                                        {/if}
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    {#each users as user}
                                        <tr class="h-14 align-middle">
                                            {#if isUserColumnVisible("avatar")}
                                                <td class="pr-4">
                                                    <Avatar class="h-10 w-10 border">
                                                        {#if user.avatar_url}
                                                            <AvatarImage
                                                                src={user.avatar_url}
                                                                alt={`${user.name} avatar`}
                                                                loading="lazy"
                                                                referrerpolicy="no-referrer"
                                                                crossorigin="anonymous"
                                                            />
                                                        {/if}
                                                        <AvatarFallback class="bg-primary/10 text-sm font-semibold text-primary">
                                                            {initials(user.name)}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                </td>
                                            {/if}
                                            {#if isUserColumnVisible("name")}
                                                <td class="pr-4 font-medium"
                                                    >{user.name}</td
                                                >
                                            {/if}
                                            {#if isUserColumnVisible("email")}
                                                <td class="pr-4 text-muted-foreground"
                                                    >{user.email}</td
                                                >
                                            {/if}
                                            {#if isUserColumnVisible("status")}
                                                <td class="pr-4">
                                                    <span
                                                        class={user.status
                                                            ? "text-emerald-600"
                                                            : "text-muted-foreground"}
                                                    >
                                                        {user.status
                                                            ? "Active"
                                                            : "Disabled"}
                                                    </span>
                                                </td>
                                            {/if}
                                            {#if isUserColumnVisible("roles")}
                                                <td class="pr-4"
                                                    >{formatRoles(user.roles)}</td
                                                >
                                            {/if}
                                            {#if isUserColumnVisible("permissions")}
                                                <td
                                                    >{formatPermissions(
                                                        user.permissions,
                                                    )}</td
                                                >
                                            {/if}
                                            {#if canManageUsers && isUserColumnVisible("actions")}
                                                <td class="pr-0 text-right">
                                                    <div
                                                        class="flex justify-end gap-2"
                                                    >
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onclick={() =>
                                                                startEditUser(user)}
                                                        >
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            disabled={isDeletingId ===
                                                                user.id}
                                                            onclick={() =>
                                                                handleDeleteUser(
                                                                    user.id,
                                                                )}
                                                        >
                                                            {isDeletingId ===
                                                            user.id
                                                                ? "Deleting..."
                                                                : "Delete"}
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
                {/if}

                {#if paginator}
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

<SlidePanel
    open={createPanelOpen}
    title="Create user"
    description="Provision a new account and set initial access."
    onClose={closeCreatePanel}
>
    {#snippet children()}
        <div class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2">
                <Input placeholder="Full name" bind:value={createForm.name} />
                <Input
                    placeholder="Email address"
                    type="email"
                    bind:value={createForm.email}
                />
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <Input
                    placeholder="Password"
                    type="password"
                    bind:value={createForm.password}
                />
                <Input
                    placeholder="Confirm password"
                    type="password"
                    bind:value={createForm.passwordConfirmation}
                />
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-foreground">
                    <input
                        type="checkbox"
                        checked={createForm.status}
                        onchange={(event) =>
                            (createForm.status = event.currentTarget.checked)}
                        class="h-4 w-4 rounded border-border text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    />
                    Active
                </label>
                {#if canManageRoles && roles.length}
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="text-muted-foreground">Roles:</span>
                        {#each roles as role}
                            <label
                                class="flex items-center gap-2 rounded-md border px-2 py-1"
                            >
                                <input
                                    type="checkbox"
                                    checked={createForm.roles.includes(
                                        role.name,
                                    )}
                                    onchange={() =>
                                        toggleRole(role.name, createForm)}
                                />
                                {role.name}
                            </label>
                        {/each}
                    </div>
                {/if}
            </div>
        </div>
    {/snippet}
    {#snippet footer()}
        <div class="flex justify-end gap-2">
            <Button
                variant="ghost"
                onclick={closeCreatePanel}
                disabled={isCreatingUser}
            >
                Cancel
            </Button>
            <Button onclick={handleCreateUser} disabled={isCreatingUser}>
                {isCreatingUser ? "Creating..." : "Create"}
            </Button>
        </div>
    {/snippet}
</SlidePanel>

{#if canManageUsers}
    <SlidePanel
        open={editPanelOpen}
        title="Edit user"
        description="Update profile, status, or roles."
        onClose={closeEditPanel}
    >
        {#snippet children()}
            <div class="space-y-3">
                <div class="grid gap-3 md:grid-cols-2">
                    <Input placeholder="Full name" bind:value={editForm.name} />
                    <Input
                        placeholder="Email address"
                        type="email"
                        bind:value={editForm.email}
                    />
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <Input
                        placeholder="New password (optional)"
                        type="password"
                        bind:value={editForm.password}
                    />
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            checked={editForm.status}
                            onchange={(event) =>
                                (editForm.status = event.currentTarget.checked)}
                            class="h-4 w-4 rounded border-border text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <span class="text-sm">Active</span>
                    </div>
                </div>
                {#if canManageRoles && roles.length}
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="text-muted-foreground">Roles:</span>
                        {#each roles as role}
                            <label
                                class="flex items-center gap-2 rounded-md border px-2 py-1"
                            >
                                <input
                                    type="checkbox"
                                    checked={editForm.roles.includes(role.name)}
                                    onchange={() =>
                                        toggleRole(role.name, editForm)}
                                />
                                {role.name}
                            </label>
                        {/each}
                    </div>
                {/if}
            </div>
        {/snippet}
        {#snippet footer()}
            <div class="flex justify-end gap-2">
                <Button
                    variant="ghost"
                    onclick={closeEditPanel}
                    disabled={isUpdatingUser}
                >
                    Cancel
                </Button>
                <Button
                    onclick={handleUpdateUser}
                    disabled={isUpdatingUser || !editingUserId}
                >
                    {isUpdatingUser ? "Saving..." : "Save changes"}
                </Button>
            </div>
        {/snippet}
    </SlidePanel>
{/if}
