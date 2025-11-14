<script lang="ts">
	import { login, persistAuth, socialLogin } from "$lib/api/auth";
	import { Button } from "$lib/components/ui/button";
	import { Input } from "$lib/components/ui/input";
	import { Label } from "$lib/components/ui/label";
	import { requestGoogleAccessToken } from "$lib/services/google";
	import { goto } from "$app/navigation";

	let { redirectTo = "/dashboard" } = $props<{ redirectTo?: string }>();

	let email = $state("");
	let password = $state("");
	let deviceName = $state("frontend");
	let isSubmitting = $state(false);
	let isGoogleSubmitting = $state(false);
	let errorMessage = $state("");
	let successMessage = $state("");

	async function handleSubmit(event: SubmitEvent) {
		event.preventDefault();
		isSubmitting = true;
		errorMessage = "";
		successMessage = "";

		try {
			const payload = await login({ email, password, deviceName });
			persistAuth(payload);
			successMessage = `Welcome back, ${payload.user.name}!`;
			goto(redirectTo || "/dashboard");
		} catch (error) {
			if (error instanceof Error) {
				errorMessage = error.message;
			} else {
				errorMessage = "Unable to sign in. Please try again.";
			}
		} finally {
			isSubmitting = false;
		}
	}

	async function handleGoogleSignIn() {
		isGoogleSubmitting = true;
		errorMessage = "";
		successMessage = "";

		try {
			const accessToken = await requestGoogleAccessToken();
			const payload = await socialLogin({
				provider: "GOOGLE",
				accessToken,
			});
			persistAuth(payload);
			successMessage = `Welcome back, ${payload.user.name}!`;
			goto(redirectTo || "/dashboard");
		} catch (error) {
			if (error instanceof Error) {
				errorMessage = error.message;
			} else {
				errorMessage = "Unable to sign in with Google. Please try again.";
			}
		} finally {
			isGoogleSubmitting = false;
		}
	}
</script>

<form class="space-y-6" onsubmit={handleSubmit}>
	<div class="space-y-4">
		<div class="space-y-1">
			<Label for="email">Email address</Label>
			<Input
				id="email"
				type="email"
				name="email"
				placeholder="you@example.com"
				autocomplete="email"
				required
				bind:value={email}
			/>
		</div>

		<div class="space-y-1">
			<Label for="password">Password</Label>
			<Input
				id="password"
				type="password"
				name="password"
				placeholder="••••••••"
				autocomplete="current-password"
				required
				bind:value={password}
			/>
		</div>
	</div>

	<input type="hidden" name="deviceName" bind:value={deviceName} />

	{#if errorMessage}
		<p class="text-sm text-destructive">{errorMessage}</p>
	{/if}

	{#if successMessage}
		<p class="text-sm text-emerald-600">{successMessage}</p>
	{/if}

	<Button class="w-full" type="submit" disabled={isSubmitting}>
		{#if isSubmitting}
			<span
				class="mr-2 inline-flex h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent"
				aria-hidden="true"
			></span>
			<span>Signing in…</span>
		{:else}
			Sign in
		{/if}
	</Button>

	<div class="flex items-center gap-3 text-sm text-muted-foreground">
		<span class="h-px flex-1 bg-border"></span>
		<span>or continue with</span>
		<span class="h-px flex-1 bg-border"></span>
	</div>

	<Button
		class="w-full"
		type="button"
		variant="outline"
		disabled={isGoogleSubmitting}
		onclick={handleGoogleSignIn}
	>
		{#if isGoogleSubmitting}
			<span
				class="mr-2 inline-flex h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent"
				aria-hidden="true"
			></span>
			<span>Connecting…</span>
		{:else}
			<svg
				class="mr-2 h-4 w-4"
				viewBox="0 0 48 48"
				aria-hidden="true"
				role="img"
				focusable="false"
			>
				<path
					fill="#EA4335"
					d="M24 9.5c3.24 0 6.15 1.11 8.45 3.28l6.31-6.31C34.9 2.14 29.99 0 24 0 14.65 0 6.57 5.38 2.59 13.22l7.53 5.84C12.13 13.28 17.52 9.5 24 9.5z"
				></path>
				<path
					fill="#4285F4"
					d="M46.98 24.55c0-1.62-.15-3.18-.44-4.68H24v9.05h13.02c-.56 2.84-2.15 5.25-4.57 6.88l7.02 5.45c4.11-3.78 6.51-9.35 6.51-15.7z"
				></path>
				<path
					fill="#FBBC05"
					d="M10.12 28.94c-.48-1.42-.76-2.94-.76-4.5s.28-3.07.76-4.5l-7.53-5.84C.89 17.56 0 20.69 0 24s.89 6.44 2.59 9.9l7.53-5.84z"
				></path>
				<path
					fill="#34A853"
					d="M24 48c5.99 0 10.97-1.98 14.63-5.37l-7.02-5.45c-2 1.35-4.57 2.13-7.61 2.13-6.48 0-11.87-3.78-13.88-9.06l-7.53 5.84C6.57 42.62 14.65 48 24 48z"
				></path>
				<path fill="none" d="M0 0h48v48H0z"></path>
			</svg>
			<span>Sign in with Google</span>
		{/if}
	</Button>
</form>
