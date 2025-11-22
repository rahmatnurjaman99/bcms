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
	import Skeleton from "$lib/components/ui/skeleton.svelte";
	import SlidePanel from "$lib/components/ui/slide-panel.svelte";
	import { browser } from "$app/environment";
	import { toast } from "svelte-sonner";
	import { createRole, deleteRole, updateRole, type RoleListItem, type PermissionListItem } from "$lib/api/admin";
	import { onMount } from "svelte";

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
	let createPanelOpen = $state(false);

	let editingRoleId = $state<string | null>(null);
	let editPanelOpen = $state(false);
	let editForm = $state({
		name: "",
		guard: "sanctum",
		permissions: [] as string[],
	});
	let isUpdating = $state(false);
	let deletingId = $state<string | null>(null);
	let loadingDirectory = $state(browser && authorized);

	onMount(() => {
		loadingDirectory = false;
	});

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
		editPanelOpen = true;
	}

	function resetCreateForm() {
		createForm = { name: "", guard: "sanctum", permissions: [] };
	}

	function closeCreatePanel() {
		createPanelOpen = false;
		resetCreateForm();
	}

	function resetEditForm() {
		editForm = { name: "", guard: "sanctum", permissions: [] };
	}

	function closeEditPanel() {
		editPanelOpen = false;
		editingRoleId = null;
		resetEditForm();
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
			closeCreatePanel();
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
			closeEditPanel();
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
	<div class="flex flex-wrap items-start justify-between gap-3">
		<div>
			<h1 class="text-3xl font-semibold tracking-tight">Roles</h1>
			<p class="text-sm text-muted-foreground">
				Understand who can access what.
			</p>
		</div>
		{#if authorized}
			<Button onclick={() => (createPanelOpen = true)}>New role</Button>
		{/if}
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
				<CardTitle>Role directory</CardTitle>
				<CardDescription
					>Every role synced from Spatie Permission.</CardDescription
				>
			</CardHeader>
			<CardContent class="space-y-4">
				{#if loadingDirectory}
					<div class="space-y-3">
						{#each Array(3) as _, index (index)}
							<div class="rounded-lg border p-4">
								<div class="flex flex-wrap items-start justify-between gap-2">
									<div class="space-y-2">
										<Skeleton class="h-4 w-32" />
										<Skeleton class="h-3 w-24" />
									</div>
									<Skeleton class="h-8 w-32" />
								</div>
								<div class="mt-3 flex flex-wrap gap-2">
									{#each Array(4) as __, idx (idx)}
										<Skeleton class="h-6 w-24" />
									{/each}
								</div>
							</div>
						{/each}
					</div>
				{/if}
				{#if roles.length === 0 && !loadingDirectory}
					<p class="text-sm text-muted-foreground">No roles yet. Create one to get started.</p>
				{:else}
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
				{/if}
			</CardContent>
		</Card>

	{/if}
</div>

<SlidePanel
	open={createPanelOpen}
	title="Create role"
	description="Add a role and choose its permissions."
	onClose={closeCreatePanel}
>
	{#snippet children()}
		<div class="space-y-3">
			<div class="grid gap-3 md:grid-cols-2">
				<Input placeholder="Role name" bind:value={createForm.name} />
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
						<label class="flex items-center gap-2 rounded-md border px-2 py-1">
							<input
								type="checkbox"
								checked={createForm.permissions.includes(permission.name)}
								onchange={() => togglePermission(permission.name, createForm)}
							/>
							{permission.name}
						</label>
					{/each}
				</div>
			{/if}
		</div>
	{/snippet}
	{#snippet footer()}
		<div class="flex justify-end gap-2">
			<Button variant="ghost" onclick={closeCreatePanel} disabled={isCreating}>
				Cancel
			</Button>
			<Button onclick={handleCreateRole} disabled={isCreating}>
				{isCreating ? "Creating..." : "Create role"}
			</Button>
		</div>
	{/snippet}
</SlidePanel>

{#if authorized}
	<SlidePanel
		open={editPanelOpen}
		title="Edit role"
		description="Rename a role or change its permissions."
		onClose={closeEditPanel}
	>
		{#snippet children()}
			<div class="space-y-3">
				<div class="grid gap-3 md:grid-cols-2">
					<Input placeholder="Role name" bind:value={editForm.name} />
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
						<span class="text-muted-foreground">Permissions:</span>
						{#each permissions as permission}
							<label class="flex items-center gap-2 rounded-md border px-2 py-1">
								<input
									type="checkbox"
									checked={editForm.permissions.includes(permission.name)}
									onchange={() => togglePermission(permission.name, editForm)}
								/>
								{permission.name}
							</label>
						{/each}
					</div>
				{/if}
			</div>
		{/snippet}
		{#snippet footer()}
			<div class="flex justify-end gap-2">
				<Button variant="ghost" onclick={closeEditPanel} disabled={isUpdating}>
					Cancel
				</Button>
				<Button onclick={handleUpdateRole} disabled={isUpdating || !editingRoleId}>
					{isUpdating ? "Saving..." : "Save changes"}
				</Button>
			</div>
		{/snippet}
	</SlidePanel>
{/if}
