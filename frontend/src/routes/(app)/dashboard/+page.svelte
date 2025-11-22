<script lang="ts">
    import type { PageData } from "./$types";
    import type { Role } from "$lib/types/user";
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from "$lib/components/ui/card";
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from "$lib/components/ui/avatar";
    import { UserRound, ShieldCheck, Key } from "@lucide/svelte";
    import { APP_NAME } from "$lib/config/app";
    import ColumnVisibilityMenu from "$lib/components/ui/column-visibility-menu.svelte";
    import {
        buildDefaultVisibility,
        loadColumnVisibility,
        persistColumnVisibility,
        type ColumnVisibilityMap,
        type ColumnVisibilityOption,
    } from "$lib/utils/column-visibility";

    let { data } = $props<{ data: PageData }>();

    const userSummary = data.userSummary;
    const roles = data.roles;
    const permissions = data.permissions;

    const formatter = new Intl.DateTimeFormat("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
    const formatRoles = (roles: Role[]) =>
        roles.map((role: Role) => role.name).join(", ") || "—";
    const initials = (fullName: string) =>
        fullName
            .split(/\s+/)
            .map((part) => part?.[0] ?? "")
            .filter(Boolean)
            .slice(0, 2)
            .join("")
            .toUpperCase();

    const DASHBOARD_RECENT_COLUMN_KEY = "dashboard-recent-columns";
    const recentColumns: ColumnVisibilityOption[] = [
        { id: "avatar", label: "Avatar" },
        { id: "name", label: "Name" },
        { id: "email", label: "Email" },
        { id: "roles", label: "Roles" },
        { id: "joined", label: "Joined" },
    ];
    const recentColumnDefaults = buildDefaultVisibility(recentColumns);
    let recentColumnVisibility = $state<ColumnVisibilityMap>(
        loadColumnVisibility(DASHBOARD_RECENT_COLUMN_KEY, recentColumns),
    );

    function toggleRecentColumn(columnId: string) {
        const current =
            recentColumnVisibility[columnId] ??
            recentColumnDefaults[columnId] ??
            true;
        recentColumnVisibility = {
            ...recentColumnVisibility,
            [columnId]: !current,
        };
    }

    function resetRecentColumns() {
        recentColumnVisibility = { ...recentColumnDefaults };
    }

    $effect(() => {
        persistColumnVisibility(
            DASHBOARD_RECENT_COLUMN_KEY,
            recentColumnVisibility,
        );
    });

    const isRecentColumnVisible = (columnId: string) =>
        recentColumnVisibility[columnId] ??
        recentColumnDefaults[columnId] ??
        true;

    function formatDate(value: string): string {
        return formatter.format(new Date(value));
    }
</script>

<svelte:head>
    <title>Dashboard | {APP_NAME}</title>
</svelte:head>

<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-semibold tracking-tight">Dashboard</h1>
        <p class="text-sm text-muted-foreground">
            Monitor team access and recent activity.
        </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {#if userSummary}
            <Card>
                <CardHeader>
                    <CardTitle
                        class="flex items-center gap-2 text-base font-semibold"
                    >
                        <UserRound class="h-4 w-4 text-primary" />
                        Total users
                    </CardTitle>
                    <CardDescription
                        >Active accounts synced with {APP_NAME}.</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold">{userSummary.total}</p>
                    <p class="text-sm text-muted-foreground">
                        Latest {userSummary.recent.length} shown below.
                    </p>
                </CardContent>
            </Card>
        {/if}

        {#if roles}
            <Card>
                <CardHeader>
                    <CardTitle
                        class="flex items-center gap-2 text-base font-semibold"
                    >
                        <ShieldCheck class="h-4 w-4 text-primary" />
                        Roles
                    </CardTitle>
                    <CardDescription>Available access levels.</CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold">{roles.length}</p>
                    <p class="text-sm text-muted-foreground">
                        Synced with Spatie Permission.
                    </p>
                </CardContent>
            </Card>
        {/if}

        {#if permissions}
            <Card>
                <CardHeader>
                    <CardTitle
                        class="flex items-center gap-2 text-base font-semibold"
                    >
                        <Key class="h-4 w-4 text-primary" />
                        Permissions
                    </CardTitle>
                    <CardDescription
                        >Granular capabilities across products.</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold">{permissions.length}</p>
                    <p class="text-sm text-muted-foreground">
                        Managed centrally.
                    </p>
                </CardContent>
            </Card>
        {/if}
    </div>

    {#if userSummary}
        <Card>
            <CardHeader>
                <CardTitle>Recent members</CardTitle>
                <CardDescription
                    >Latest accounts created in the last sync window.</CardDescription
                >
            </CardHeader>
            <CardContent class="space-y-2">
                <div class="flex justify-end">
                    <ColumnVisibilityMenu
                        columns={recentColumns}
                        visibility={recentColumnVisibility}
                        toggleColumn={toggleRecentColumn}
                        resetColumns={resetRecentColumns}
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="text-xs uppercase text-muted-foreground">
                            <tr>
                                {#if isRecentColumnVisible("avatar")}
                                    <th class="pb-2">Avatar</th>
                                {/if}
                                {#if isRecentColumnVisible("name")}
                                    <th class="pb-2">Name</th>
                                {/if}
                                {#if isRecentColumnVisible("email")}
                                    <th class="pb-2">Email</th>
                                {/if}
                                {#if isRecentColumnVisible("roles")}
                                    <th class="pb-2">Roles</th>
                                {/if}
                                {#if isRecentColumnVisible("joined")}
                                    <th class="pb-2">Joined</th>
                                {/if}
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each userSummary.recent as user}
                                <tr class="h-16 align-middle">
                                    {#if isRecentColumnVisible("avatar")}
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
                                    {#if isRecentColumnVisible("name")}
                                        <td class="pr-4 font-medium">{user.name}</td>
                                    {/if}
                                    {#if isRecentColumnVisible("email")}
                                        <td class="pr-4 text-muted-foreground"
                                            >{user.email}</td
                                        >
                                    {/if}
                                    {#if isRecentColumnVisible("roles")}
                                        <td class="pr-4">{formatRoles(user.roles)}</td>
                                    {/if}
                                    {#if isRecentColumnVisible("joined")}
                                        <td class="text-muted-foreground"
                                            >{formatDate(user.created_at)}</td
                                        >
                                    {/if}
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    {:else}
        <Card>
            <CardHeader>
                <CardTitle>Users</CardTitle>
                <CardDescription
                    >Insufficient permission to view user stats.</CardDescription
                >
            </CardHeader>
        </Card>
    {/if}
</div>
