<script lang="ts">
	import LoginForm from "$lib/components/forms/LoginForm.svelte";
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "$lib/components/ui/card";
	import type { PageData } from "./$types";
	import { APP_NAME } from "$lib/config/app";

	let { data } = $props<{ data: PageData }>();
</script>

<svelte:head>
	<title>Sign in | {APP_NAME}</title>
	<meta name="description" content={`Sign in to access your ${APP_NAME} workspace.`} />
</svelte:head>

<div class="min-h-screen w-full bg-background lg:grid lg:grid-cols-2">
	<section class="relative hidden flex-col justify-between bg-gradient-to-br from-primary to-primary/70 p-10 text-primary-foreground lg:flex">
		<div class="space-y-6">
			<p class="text-sm uppercase tracking-[0.3em]">{APP_NAME}</p>
			<h2 class="text-4xl font-semibold leading-tight">
				Streamline access to every site and campaign from a single, secure dashboard.
			</h2>
			<p class="max-w-md text-base text-primary-foreground/80">
				Granular roles, audit trails, and near real-time search ensure your teams ship confidently without
				sacrificing governance.
			</p>
		</div>
		<div class="space-y-4 text-sm text-primary-foreground/80">
			<div>
				<p class="font-semibold text-primary-foreground">Adaptive security</p>
				<p>Device-based tokens keep every session scoped and revokable.</p>
			</div>
			<div>
				<p class="font-semibold text-primary-foreground">Unified permissions</p>
				<p>Centralize Spatie-driven roles across multiple client properties.</p>
			</div>
		</div>
	</section>

	<section class="flex items-center justify-center px-6 py-12 sm:px-12">
		<div class="w-full max-w-md space-y-6">
			<div class="space-y-2 text-center lg:text-left">
				<p class="text-sm font-semibold text-primary">Welcome back</p>
				<h1 class="text-3xl font-semibold tracking-tight text-foreground">Sign in to {APP_NAME}</h1>
				<p class="text-sm text-muted-foreground">Use your admin credentials to access the control center.</p>
			</div>

			<Card>
				<CardHeader>
					<CardTitle>Credentials</CardTitle>
					<CardDescription>Enter your email address and password.</CardDescription>
				</CardHeader>
				<CardContent>
					<LoginForm redirectTo={data.redirectTo} />
				</CardContent>
			</Card>

			<p class="text-center text-sm text-muted-foreground">
				New here?
				<a class="font-medium text-primary hover:text-primary/80" href="/register">Create an account</a>
			</p>
		</div>
	</section>
</div>
