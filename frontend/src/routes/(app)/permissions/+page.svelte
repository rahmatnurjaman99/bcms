<script lang="ts">
	import type { PageData } from "./$types";
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "$lib/components/ui/card";
	import { APP_NAME } from "$lib/config/app";

	let { data } = $props<{ data: PageData }>();
</script>

<div class="space-y-6">
	<div>
		<h1 class="text-3xl font-semibold tracking-tight">Permissions</h1>
		<p class="text-sm text-muted-foreground">Fine-grained actions available across {APP_NAME}.</p>
	</div>

	{#if !data.authorized}
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
				<CardDescription>{data.permissions.length} permissions defined.</CardDescription>
			</CardHeader>
			<CardContent class="grid gap-3 md:grid-cols-2">
				{#each data.permissions as permission}
					<div class="rounded-lg border p-3">
						<p class="text-sm font-semibold">{permission.name}</p>
						<p class="text-xs text-muted-foreground">Guard: {permission.guard_name}</p>
					</div>
				{/each}
			</CardContent>
		</Card>
	{/if}
</div>
