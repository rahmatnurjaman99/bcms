<script lang="ts">
	import { register, persistAuth } from "$lib/api/auth";
	import { Button } from "$lib/components/ui/button";
	import { Input } from "$lib/components/ui/input";
	import { Label } from "$lib/components/ui/label";
	import { goto } from "$app/navigation";

	let name = $state("");
	let email = $state("");
	let password = $state("");
	let passwordConfirmation = $state("");
	let deviceName = $state("frontend");
	let isSubmitting = $state(false);
	let errorMessage = $state("");
	let successMessage = $state("");

	async function handleSubmit(event: SubmitEvent) {
		event.preventDefault();
		if (password !== passwordConfirmation) {
			errorMessage = "Passwords do not match.";
			return;
		}

		isSubmitting = true;
		errorMessage = "";
		successMessage = "";

		try {
			const payload = await register({
				name,
				email,
				password,
				passwordConfirmation,
				deviceName
			});
			persistAuth(payload);
			successMessage = `Account created for ${payload.user.name}.`;
			goto("/dashboard");
		} catch (error) {
			if (error instanceof Error) {
				errorMessage = error.message;
			} else {
				errorMessage = "Unable to register. Please try again.";
			}
		} finally {
			isSubmitting = false;
		}
	}
</script>

<form class="space-y-6" onsubmit={handleSubmit}>
	<div class="space-y-4">
		<div class="space-y-1">
			<Label for="name">Full name</Label>
			<Input
				id="name"
				name="name"
				placeholder="Jane Doe"
				autocomplete="name"
				required
				bind:value={name}
			/>
		</div>

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
				autocomplete="new-password"
				minlength={8}
				required
				bind:value={password}
			/>
		</div>

		<div class="space-y-1">
			<Label for="passwordConfirmation">Confirm password</Label>
			<Input
				id="passwordConfirmation"
				type="password"
				name="passwordConfirmation"
				placeholder="••••••••"
				autocomplete="new-password"
				required
				bind:value={passwordConfirmation}
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
			<span>Creating account…</span>
		{:else}
			Create account
		{/if}
	</Button>
</form>
