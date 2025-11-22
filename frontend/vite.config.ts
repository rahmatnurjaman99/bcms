import tailwindcss from '@tailwindcss/vite';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
	const env = loadEnv(mode, process.cwd(), '');

	return {
		plugins: [tailwindcss(), sveltekit()],
		server: {
			host: env.VITE_HOST || '0.0.0.0'
		},
		ssr: {
			noExternal: ['svelte-sonner', 'mode-watcher']
		}
	};
});
