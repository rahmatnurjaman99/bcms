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
    import { UserRound, ShieldCheck, Key } from "@lucide/svelte";
    import { APP_NAME } from "$lib/config/app";

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
            <CardContent class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead class="text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="pb-2">Name</th>
                            <th class="pb-2">Email</th>
                            <th class="pb-2">Roles</th>
                            <th class="pb-2">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        {#each userSummary.recent as user}
                            <tr class="h-12">
                                <td class="pr-4 font-medium">{user.name}</td>
                                <td class="pr-4 text-muted-foreground"
                                    >{user.email}</td
                                >
                                <td class="pr-4">{formatRoles(user.roles)}</td>
                                <td class="text-muted-foreground"
                                    >{formatDate(user.created_at)}</td
                                >
                            </tr>
                        {/each}
                    </tbody>
                </table>
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
