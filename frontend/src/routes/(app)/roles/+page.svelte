<script lang="ts">
    import type { PageData } from "./$types";
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from "$lib/components/ui/card";
	import { APP_NAME } from "$lib/config/app";
	import { Input } from "$lib/components/ui/input";
	import { Button } from "$lib/components/ui/button";
	import { toast } from "svelte-sonner";
	import { createRole, deleteRole, updateRole, type RoleListItem, type PermissionListItem } from "$lib/api/admin";

    let { data } = $props<{ data: PageData }>();

    const authorized = data.authorized;
	let roles = $state<RoleListItem[]>(data.roles ?? []);
	let permissions = $state<PermissionListItem[]>(data.permissions ?? []);

    let createForm = $state({
        name: "",
        guard: "sanctum",
        permissions: [] as string[],
    });
    let isCreating = $state(false);

    let editingRoleId = $state<string | null>(null);
    let editForm = $state({
        name: "",
        guard: "sanctum",
        permissions: [] as string[],
    });
    let isUpdating = $state(false);
    let deletingId = $state<string | null>(null);

    function togglePermission(name: string, target: { permissions: string[] }) {
        target.permissions = target.permissions.includes(name)
            ? target.permissions.filter((perm) => perm !== name)
            : [...target.permissions, name];
    }

	function startEdit(role: RoleListItem) {
		editingRoleId = role.id;
		editForm = {
			name: role.name,
			guard: role.guard_name,
			permissions: role.permissions.map((perm) => perm.name),
        };
    }

    function resetCreateForm() {
        createForm = { name: "", guard: "sanctum", permissions: [] };
    }

    async function handleCreateRole() {
        isCreating = true;
        try {
            const newRole = await createRole({
                name: createForm.name,
                guard: createForm.guard || undefined,
                permissions: createForm.permissions,
                fetchImpl: fetch,
            });
            roles = [newRole, ...roles];
            toast.success("Role created");
            resetCreateForm();
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Unable to create role",
            );
        } finally {
            isCreating = false;
        }
    }

    async function handleUpdateRole() {
        if (!editingRoleId) return;
        isUpdating = true;
        try {
            const updated = await updateRole({
                id: editingRoleId,
                name: editForm.name,
                guard: editForm.guard || undefined,
                permissions: editForm.permissions,
                fetchImpl: fetch,
            });
            roles = roles.map((role) =>
                role.id === updated.id ? updated : role,
            );
            toast.success("Role updated");
            editingRoleId = null;
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Unable to update role",
            );
        } finally {
            isUpdating = false;
        }
    }

    async function handleDeleteRole(id: string) {
        if (!confirm("Delete this role?")) return;
        deletingId = id;
        try {
            const deleted = await deleteRole({ id, fetchImpl: fetch });
            if (deleted) {
                roles = roles.filter((role) => role.id !== id);
                toast.success("Role deleted");
            }
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Unable to delete role",
            );
        } finally {
            deletingId = null;
        }
    }
</script>

<svelte:head>
    <title>Roles | {APP_NAME}</title>
</svelte:head>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-semibold tracking-tight">Roles</h1>
        <p class="text-sm text-muted-foreground">
            Understand who can access what.
        </p>
    </div>

    {#if !authorized}
        <Card>
            <CardHeader>
                <CardTitle>No access</CardTitle>
                <CardDescription
                    >You need roles.manage permission to view this section.</CardDescription
                >
            </CardHeader>
        </Card>
    {:else}
        <Card>
            <CardHeader>
                <CardTitle>Create role</CardTitle>
                <CardDescription
                    >Add a role and choose its permissions.</CardDescription
                >
            </CardHeader>
            <CardContent class="space-y-3">
                <div class="grid gap-3 md:grid-cols-2">
                    <Input
                        placeholder="Role name"
                        bind:value={createForm.name}
                    />
                    <select
                        class="h-10 rounded-md border border-border bg-background px-3 text-sm"
                        bind:value={createForm.guard}
                    >
                        <option value="sanctum">sanctum</option>
                        <option value="web">web</option>
                    </select>
                </div>
                {#if permissions.length}
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="text-muted-foreground">Permissions:</span>
                        {#each permissions as permission}
                            <label
                                class="flex items-center gap-2 rounded-md border px-2 py-1"
                            >
                                <input
                                    type="checkbox"
                                    checked={createForm.permissions.includes(
                                        permission.name,
                                    )}
                                    onchange={() =>
                                        togglePermission(
                                            permission.name,
                                            createForm,
                                        )}
                                />
                                {permission.name}
                            </label>
                        {/each}
                    </div>
                {/if}
                <Button
                    class="w-full md:w-auto"
                    onclick={handleCreateRole}
                    disabled={isCreating}
                >
                    {isCreating ? "Creating..." : "Create role"}
                </Button>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Role directory</CardTitle>
                <CardDescription
                    >Every role synced from Spatie Permission.</CardDescription
                >
            </CardHeader>
            <CardContent class="space-y-4">
                {#each roles as role}
                    <div class="rounded-lg border p-4">
                        <div
                            class="flex flex-wrap items-start justify-between gap-2"
                        >
                            <div>
                                <p class="text-base font-semibold">
                                    {role.name}
                                </p>
                                <p
                                    class="text-xs uppercase tracking-wide text-muted-foreground"
                                >
                                    Guard: {role.guard_name}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-muted-foreground">
                                    Updated {new Date(
                                        role.updated_at,
                                    ).toLocaleString()}
                                </p>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    onclick={() => startEdit(role)}>Edit</Button
                                >
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    disabled={deletingId === role.id}
                                    onclick={() => handleDeleteRole(role.id)}
                                >
                                    {deletingId === role.id
                                        ? "Deleting..."
                                        : "Delete"}
                                </Button>
                            </div>
                        </div>
                        <div class="mt-3 text-sm">
                            <p class="font-medium">Permissions</p>
                            {#if role.permissions.length > 0}
                                <div class="mt-1 flex flex-wrap gap-2">
                                    {#each role.permissions as permission}
                                        <span
                                            class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground"
                                        >
                                            {permission.name}
                                        </span>
                                    {/each}
                                </div>
                            {:else}
                                <p class="text-muted-foreground">
                                    No permissions assigned.
                                </p>
                            {/if}
                        </div>
                    </div>
                {/each}
            </CardContent>
        </Card>

        {#if editingRoleId}
            <Card>
                <CardHeader>
                    <CardTitle>Edit role</CardTitle>
                    <CardDescription
                        >Rename a role or change its permissions.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="grid gap-3 md:grid-cols-2">
                        <Input
                            placeholder="Role name"
                            bind:value={editForm.name}
                        />
                        <select
                            class="h-10 rounded-md border border-border bg-background px-3 text-sm"
                            bind:value={editForm.guard}
                        >
                            <option value="sanctum">sanctum</option>
                            <option value="web">web</option>
                        </select>
                    </div>
                    {#if permissions.length}
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="text-muted-foreground"
                                >Permissions:</span
                            >
                            {#each permissions as permission}
                                <label
                                    class="flex items-center gap-2 rounded-md border px-2 py-1"
                                >
                                    <input
                                        type="checkbox"
                                        checked={editForm.permissions.includes(
                                            permission.name,
                                        )}
                                        onchange={() =>
                                            togglePermission(
                                                permission.name,
                                                editForm,
                                            )}
                                    />
                                    {permission.name}
                                </label>
                            {/each}
                        </div>
                    {/if}
                    <div class="flex flex-wrap gap-2">
                        <Button
                            onclick={handleUpdateRole}
                            disabled={isUpdating}
                        >
                            {isUpdating ? "Saving..." : "Save changes"}
                        </Button>
                        <Button
                            variant="ghost"
                            onclick={() => (editingRoleId = null)}
                            disabled={isUpdating}
                        >
                            Cancel
                        </Button>
                    </div>
                </CardContent>
            </Card>
        {/if}
    {/if}
</div>
