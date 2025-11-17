<script lang="ts">
	import type { PageData } from "./$types";
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "$lib/components/ui/card";
	import { APP_NAME } from "$lib/config/app";
	import { Input } from "$lib/components/ui/input";
	import { Button } from "$lib/components/ui/button";
	import { toast } from "svelte-sonner";
	import { createPermission, deletePermission, updatePermission } from "$lib/api/admin";

	let { data } = $props<{ data: PageData }>();

	const authorized = data.authorized;
	let permissions = $state(data.permissions ?? []);

	let createForm = $state({ name: "", guard: "sanctum" });
	let isCreating = $state(false);

	let editingId = $state<string | null>(null);
	let editForm = $state({ name: "", guard: "sanctum" });
	let isUpdating = $state(false);
	let deletingId = $state<string | null>(null);

	function startEdit(permission) {
		editingId = permission.id;
		editForm = {
			name: permission.name,
			guard: permission.guard_name,
		};
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
				createForm = { name: "", guard: "sanctum" };
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
			permissions = permissions.map((perm) => (perm.id === updated.id ? updated : perm));
			toast.success("Permission updated");
			editingId = null;
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
				permissions = permissions.filter((perm) => perm.id !== id);
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
	<div>
		<h1 class="text-3xl font-semibold tracking-tight">Permissions</h1>
		<p class="text-sm text-muted-foreground">Fine-grained actions available across {APP_NAME}.</p>
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
				<CardTitle>Create permission</CardTitle>
				<CardDescription>Add a new capability to the system.</CardDescription>
			</CardHeader>
			<CardContent class="space-y-3">
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
				<Button class="w-full md:w-auto" onclick={handleCreatePermission} disabled={isCreating}>
					{isCreating ? "Creating..." : "Create permission"}
				</Button>
			</CardContent>
		</Card>

		<Card>
			<CardHeader>
				<CardTitle>Permission registry</CardTitle>
				<CardDescription>{permissions.length} permissions defined.</CardDescription>
			</CardHeader>
			<CardContent class="grid gap-3 md:grid-cols-2">
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
			</CardContent>
		</Card>

		{#if editingId}
			<Card>
				<CardHeader>
					<CardTitle>Edit permission</CardTitle>
					<CardDescription>Rename or change the guard.</CardDescription>
				</CardHeader>
				<CardContent class="space-y-3">
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
					<div class="flex flex-wrap gap-2">
						<Button onclick={handleUpdatePermission} disabled={isUpdating}>
							{isUpdating ? "Saving..." : "Save changes"}
						</Button>
						<Button variant="ghost" onclick={() => (editingId = null)} disabled={isUpdating}>
							Cancel
						</Button>
					</div>
				</CardContent>
			</Card>
		{/if}
	{/if}
</div>
