<script lang="ts">
	import RegisterForm from "$lib/components/forms/RegisterForm.svelte";
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "$lib/components/ui/card";
	import { APP_NAME } from "$lib/config/app";
</script>

<svelte:head>
	<title>Create account | {APP_NAME}</title>
	<meta name="description" content={`Create a ${APP_NAME} account to start managing your sites and users.`} />
</svelte:head>

<div class="min-h-screen w-full bg-background lg:grid lg:grid-cols-2">
	<section class="relative hidden flex-col justify-between bg-muted p-10 lg:flex">
		<div class="space-y-6">
			<p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">{APP_NAME}</p>
			<h2 class="text-4xl font-semibold leading-tight text-foreground">
				Launch new projects faster with role-aware workflows and instant search.
			</h2>
			<p class="max-w-md text-base text-muted-foreground">
				Spin up secure environments for every brand partner without touching infrastructure configuration.
			</p>
		</div>
		<div class="space-y-4 text-sm text-muted-foreground">
			<div>
				<p class="font-semibold text-foreground">Invite collaborators</p>
				<p>Assign roles per environment and keep responsibilities clear.</p>
			</div>
			<div>
				<p class="font-semibold text-foreground">Stay compliant</p>
				<p>Centralized audit logs and permission policies out of the box.</p>
			</div>
		</div>
	</section>

	<section class="flex items-center justify-center px-6 py-12 sm:px-12">
		<div class="w-full max-w-md space-y-6">
			<div class="space-y-2 text-center lg:text-left">
				<p class="text-sm font-semibold text-primary">Create account</p>
				<h1 class="text-3xl font-semibold tracking-tight text-foreground">Join {APP_NAME}</h1>
				<p class="text-sm text-muted-foreground">
					We will automatically assign the appropriate default role after verifying your email.
				</p>
			</div>

			<Card>
				<CardHeader>
					<CardTitle>Account details</CardTitle>
					<CardDescription>Use your work email and a strong password.</CardDescription>
				</CardHeader>
				<CardContent>
					<RegisterForm />
				</CardContent>
			</Card>

			<p class="text-center text-sm text-muted-foreground">
				Already have access?
				<a class="font-medium text-primary hover:text-primary/80" href="/login">Sign in</a>
			</p>
		</div>
	</section>
</div>
