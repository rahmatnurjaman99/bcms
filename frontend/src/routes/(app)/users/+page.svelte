<script lang="ts">
	import type { PageData } from "./$types";
	import type { Permission, Role } from "$lib/types/user";
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "$lib/components/ui/card";
	import { Input } from "$lib/components/ui/input";
	import { Button } from "$lib/components/ui/button";
	import { APP_NAME } from "$lib/config/app";

	let { data } = $props<{ data: PageData }>();

	const authorized = data.authorized;
	const listing = data.users;

	const searchValue = data.search ?? "";

	const paginator = listing?.paginatorInfo;

	const formatRoles = (roles: Role[]) => roles.map((role: Role) => role.name).join(", ") || "—";
	const formatPermissions = (permissions: Permission[]) =>
		permissions.map((perm: Permission) => perm.name).join(", ") || "—";

	const queryString = (page: number) => {
		const params = new URLSearchParams();
		if (searchValue) params.set("search", searchValue);
		if (page > 1) params.set("page", page.toString());
		return params.toString() ? `?${params.toString()}` : "";
	};
</script>

<div class="space-y-6">
	<div>
		<h1 class="text-3xl font-semibold tracking-tight">Users</h1>
		<p class="text-sm text-muted-foreground">Manage team members and their roles.</p>
	</div>

	{#if !authorized}
		<Card>
			<CardHeader>
				<CardTitle>No access</CardTitle>
				<CardDescription>You do not have permission to view users.</CardDescription>
			</CardHeader>
		</Card>
	{:else if listing}
		<Card>
			<CardHeader class="gap-4 md:flex-row md:items-center md:justify-between">
				<div>
					<CardTitle>Directory</CardTitle>
					<CardDescription>
						{listing.paginatorInfo.total} members synced from {APP_NAME}.
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
				<div class="overflow-x-auto">
					<table class="w-full min-w-[800px] text-left text-sm">
						<thead class="text-xs uppercase text-muted-foreground">
							<tr>
								<th class="pb-2">Name</th>
								<th class="pb-2">Email</th>
								<th class="pb-2">Status</th>
								<th class="pb-2">Roles</th>
								<th class="pb-2">Permissions</th>
							</tr>
						</thead>
						<tbody class="divide-y">
							{#each listing.data as user}
								<tr class="h-14">
									<td class="pr-4 font-medium">{user.name}</td>
									<td class="pr-4 text-muted-foreground">{user.email}</td>
									<td class="pr-4">
										<span class={user.status ? "text-emerald-600" : "text-muted-foreground"}>
											{user.status ? "Active" : "Disabled"}
										</span>
									</td>
									<td class="pr-4">{formatRoles(user.roles)}</td>
									<td>{formatPermissions(user.permissions)}</td>
								</tr>
							{/each}
						</tbody>
					</table>
				</div>

				{#if paginator}
					<div class="flex items-center justify-between text-sm text-muted-foreground">
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
			</CardContent>
		</Card>
	{/if}
</div>
