<script lang="ts">
	import type { PageData } from "./$types";
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "$lib/components/ui/card";
	import { APP_NAME } from "$lib/config/app";
	import { Input } from "$lib/components/ui/input";
	import { Button } from "$lib/components/ui/button";
	import Skeleton from "$lib/components/ui/skeleton.svelte";
	import SlidePanel from "$lib/components/ui/slide-panel.svelte";
	import { browser } from "$app/environment";
	import { toast } from "svelte-sonner";
	import { createPermission, deletePermission, updatePermission, type PermissionListItem } from "$lib/api/admin";
	import { onMount } from "svelte";

	let { data } = $props<{ data: PageData }>();

	const authorized = data.authorized;
	let permissions = $state(data.permissions ?? []);

	let createForm = $state({ name: "", guard: "sanctum" });
	let isCreating = $state(false);
	let createPanelOpen = $state(false);

	let editingId = $state<string | null>(null);
	let editPanelOpen = $state(false);
	let editForm = $state({ name: "", guard: "sanctum" });
	let isUpdating = $state(false);
	let deletingId = $state<string | null>(null);
	let loadingRegistry = $state(browser && authorized);

	onMount(() => {
		loadingRegistry = false;
	});

	function startEdit(permission: PermissionListItem) {
		editingId = permission.id;
		editForm = {
			name: permission.name,
			guard: permission.guard_name,
		};
		editPanelOpen = true;
	}

	function closeCreatePanel() {
		createPanelOpen = false;
		createForm = { name: "", guard: "sanctum" };
	}

	function closeEditPanel() {
		editPanelOpen = false;
		editingId = null;
		editForm = { name: "", guard: "sanctum" };
	}

	async function handleCreatePermission() {
		isCreating = true;
		try {
			const created = await createPermission({
				name: createForm.name,
				guard: createForm.guard || undefined,
				fetchImpl: fetch,
			});
			permissions = [created, ...permissions];
			toast.success("Permission created");
			closeCreatePanel();
		} catch (error) {
			toast.error(error instanceof Error ? error.message : "Unable to create permission");
		} finally {
			isCreating = false;
		}
	}

	async function handleUpdatePermission() {
		if (!editingId) return;
		isUpdating = true;
		try {
			const updated = await updatePermission({
				id: editingId,
				name: editForm.name,
				guard: editForm.guard || undefined,
				fetchImpl: fetch,
			});
			permissions = permissions.map((perm: PermissionListItem) =>
				perm.id === updated.id ? updated : perm
			);
			toast.success("Permission updated");
			closeEditPanel();
		} catch (error) {
			toast.error(error instanceof Error ? error.message : "Unable to update permission");
		} finally {
			isUpdating = false;
		}
	}

	async function handleDeletePermission(id: string) {
		if (!confirm("Delete this permission?")) return;
		deletingId = id;
		try {
			const deleted = await deletePermission({ id, fetchImpl: fetch });
			if (deleted) {
				permissions = permissions.filter((perm: PermissionListItem) => perm.id !== id);
				toast.success("Permission deleted");
			}
		} catch (error) {
			toast.error(error instanceof Error ? error.message : "Unable to delete permission");
		} finally {
			deletingId = null;
		}
	}
</script>

<svelte:head>
	<title>Permissions | {APP_NAME}</title>
</svelte:head>

<div class="space-y-6">
	<div class="flex flex-wrap items-start justify-between gap-3">
		<div>
			<h1 class="text-3xl font-semibold tracking-tight">Permissions</h1>
			<p class="text-sm text-muted-foreground">Fine-grained actions available across {APP_NAME}.</p>
		</div>
		{#if authorized}
			<Button onclick={() => (createPanelOpen = true)}>New permission</Button>
		{/if}
	</div>

	{#if !authorized}
		<Card>
			<CardHeader>
				<CardTitle>No access</CardTitle>
				<CardDescription>Only administrators can view permissions.</CardDescription>
			</CardHeader>
		</Card>
	{:else}
		<Card>
			<CardHeader>
				<CardTitle>Permission registry</CardTitle>
				<CardDescription>{permissions.length} permissions defined.</CardDescription>
			</CardHeader>
			<CardContent class="grid gap-3 md:grid-cols-2">
				{#if loadingRegistry}
					{#each Array(4) as _, index (index)}
						<div class="space-y-3 rounded-lg border p-3">
							<Skeleton class="h-4 w-40" />
							<Skeleton class="h-3 w-24" />
							<div class="flex gap-2">
								<Skeleton class="h-8 w-20" />
								<Skeleton class="h-8 w-20" />
							</div>
						</div>
					{/each}
				{/if}
				{#if permissions.length === 0 && !loadingRegistry}
					<p class="text-sm text-muted-foreground md:col-span-2">No permissions yet.</p>
				{:else}
					{#each permissions as permission}
						<div class="rounded-lg border p-3 space-y-2">
							<div class="flex items-start justify-between gap-2">
								<div>
									<p class="text-sm font-semibold">{permission.name}</p>
									<p class="text-xs text-muted-foreground">Guard: {permission.guard_name}</p>
								</div>
								<div class="flex gap-2">
									<Button size="sm" variant="outline" onclick={() => startEdit(permission)}>Edit</Button>
									<Button
										size="sm"
										variant="destructive"
										disabled={deletingId === permission.id}
										onclick={() => handleDeletePermission(permission.id)}
									>
										{deletingId === permission.id ? "Deleting..." : "Delete"}
									</Button>
								</div>
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
	title="Create permission"
	description="Add a new capability to the system."
	onClose={closeCreatePanel}
>
	{#snippet children()}
		<div class="space-y-3">
			<div class="grid gap-3 md:grid-cols-2">
				<Input placeholder="Permission name" bind:value={createForm.name} />
				<select
					class="h-10 rounded-md border border-border bg-background px-3 text-sm"
					bind:value={createForm.guard}
				>
					<option value="sanctum">sanctum</option>
					<option value="web">web</option>
				</select>
			</div>
		</div>
	{/snippet}
	{#snippet footer()}
		<div class="flex justify-end gap-2">
			<Button variant="ghost" onclick={closeCreatePanel} disabled={isCreating}>
				Cancel
			</Button>
			<Button onclick={handleCreatePermission} disabled={isCreating}>
				{isCreating ? "Creating..." : "Create permission"}
			</Button>
		</div>
	{/snippet}
</SlidePanel>

{#if authorized}
	<SlidePanel
		open={editPanelOpen}
		title="Edit permission"
		description="Rename or change the guard."
		onClose={closeEditPanel}
	>
		{#snippet children()}
			<div class="space-y-3">
				<div class="grid gap-3 md:grid-cols-2">
					<Input placeholder="Permission name" bind:value={editForm.name} />
					<select
						class="h-10 rounded-md border border-border bg-background px-3 text-sm"
						bind:value={editForm.guard}
					>
						<option value="sanctum">sanctum</option>
						<option value="web">web</option>
					</select>
				</div>
			</div>
		{/snippet}
		{#snippet footer()}
			<div class="flex justify-end gap-2">
				<Button variant="ghost" onclick={closeEditPanel} disabled={isUpdating}>
					Cancel
				</Button>
				<Button onclick={handleUpdatePermission} disabled={isUpdating || !editingId}>
					{isUpdating ? "Saving..." : "Save changes"}
				</Button>
			</div>
		{/snippet}
	</SlidePanel>
{/if}
