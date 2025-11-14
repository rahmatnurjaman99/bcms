<script lang="ts">
	import { buttonVariants } from "$lib/components/ui/button";
	import { APP_NAME } from "$lib/config/app";
</script>

<svelte:head>
	<title>{APP_NAME} | Content Management Suite</title>
	<meta name="description" content={`Sign in to manage sites, users, and roles with the ${APP_NAME} dashboard.`} />
</svelte:head>

<main class="flex min-h-screen flex-col bg-background">
	<section class="relative flex flex-1 flex-col items-center justify-center px-6 py-16 sm:px-12 lg:flex-row">
		<div class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
		<div class="mx-auto flex w-full max-w-5xl flex-col items-center gap-12 lg:flex-row">
			<div class="space-y-6 text-center lg:text-left">
				<p class="text-sm font-semibold uppercase tracking-widest text-primary">{APP_NAME} platform</p>
				<h1 class="text-balance text-4xl font-semibold tracking-tight text-foreground sm:text-5xl">
					Manage your content, teams, and permissions from a single secure dashboard.
				</h1>
				<p class="max-w-2xl text-lg text-muted-foreground">
					Access granular role management, monitor sites, and keep every brand touch point consistent.
					Start by signing in or creating an account for your organization.
				</p>
				<div class="flex flex-wrap items-center gap-4">
					<a href="/login" class={buttonVariants({ size: "lg" })}>Sign in</a>
					<a
						href="/register"
						class={buttonVariants({ size: "lg", variant: "outline" })}
					>
						Create account
					</a>
				</div>
			</div>
			<div class="w-full max-w-lg rounded-3xl border border-border/70 bg-card/80 p-6 shadow-lg backdrop-blur">
				<ul class="space-y-4 text-sm text-muted-foreground">
					<li class="flex items-start gap-3">
						<span class="mt-1 h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
						<span>SSO-ready GraphQL API with token-based authentication.</span>
					</li>
					<li class="flex items-start gap-3">
						<span class="mt-1 h-2 w-2 rounded-full bg-sky-500" aria-hidden="true"></span>
						<span>Role and permission tooling backed by Spatie Laravel Permission.</span>
					</li>
					<li class="flex items-start gap-3">
						<span class="mt-1 h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
						<span>Auditable actions and secure device tokens for every session.</span>
					</li>
				</ul>
			</div>
		</div>
	</section>
</main>
