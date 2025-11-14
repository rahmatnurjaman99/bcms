<script lang="ts">
	import type { PageData } from "./$types";
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "$lib/components/ui/card";

	let { data } = $props<{ data: PageData }>();

	const authorized = data.authorized;
</script>

<div class="space-y-6">
	<div>
		<h1 class="text-3xl font-semibold tracking-tight">Roles</h1>
		<p class="text-sm text-muted-foreground">Understand who can access what.</p>
	</div>

	{#if !authorized}
		<Card>
			<CardHeader>
				<CardTitle>No access</CardTitle>
				<CardDescription>You need roles.manage permission to view this section.</CardDescription>
			</CardHeader>
		</Card>
	{:else}
		<Card>
			<CardHeader>
				<CardTitle>Role directory</CardTitle>
				<CardDescription>Every role synced from Spatie Permission.</CardDescription>
			</CardHeader>
			<CardContent class="space-y-4">
				{#each data.roles as role}
					<div class="rounded-lg border p-4">
						<div class="flex flex-wrap items-start justify-between gap-2">
							<div>
								<p class="text-base font-semibold">{role.name}</p>
								<p class="text-xs uppercase tracking-wide text-muted-foreground">
									Guard: {role.guard_name}
								</p>
							</div>
							<p class="text-xs text-muted-foreground">Updated {new Date(role.updated_at).toLocaleString()}</p>
						</div>
						<div class="mt-3 text-sm">
							<p class="font-medium">Permissions</p>
							{#if role.permissions.length > 0}
								<div class="mt-1 flex flex-wrap gap-2">
									{#each role.permissions as permission}
										<span class="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">
											{permission.name}
										</span>
									{/each}
								</div>
							{:else}
								<p class="text-muted-foreground">No permissions assigned.</p>
							{/if}
						</div>
					</div>
				{/each}
			</CardContent>
		</Card>
	{/if}
</div>
