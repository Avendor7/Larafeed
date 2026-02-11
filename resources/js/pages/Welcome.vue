<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, Newspaper } from 'lucide-vue-next';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen bg-background text-foreground">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
            <div class="flex items-center gap-3">
                <div class="rounded-lg border border-fuchsia-500/20 bg-fuchsia-500/10 p-2">
                    <Newspaper class="h-5 w-5 text-fuchsia-400" />
                </div>
                <div class="text-sm uppercase tracking-[0.35em] text-muted-foreground">
                    Larafeed
                </div>
            </div>
            <nav class="flex items-center gap-4 text-sm">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded-full border border-fuchsia-500/30 px-4 py-2 text-foreground transition hover:border-fuchsia-400/70"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="rounded-full border border-transparent px-4 py-2 text-muted-foreground transition hover:border-fuchsia-500/20 hover:text-foreground"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="rounded-full border border-fuchsia-500/30 px-4 py-2 text-foreground transition hover:border-fuchsia-400/70"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-8 lg:grid-cols-[1.2fr,0.8fr]">
            <section class="space-y-8">
                <div class="space-y-4">
                    <p class="text-xs uppercase tracking-[0.35em] text-fuchsia-400/70">
                        Nightshift Mode
                    </p>
                    <h1 class="text-4xl font-semibold leading-tight md:text-5xl">
                        Read the web like a signal desk.
                    </h1>
                    <p class="max-w-xl text-base text-muted-foreground">
                        Larafeed keeps your RSS flow tight and readable. Tune sources,
                        refresh on demand, and keep a consistent, low-glare workspace.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        :href="dashboard()"
                        class="inline-flex items-center gap-2 rounded-full border border-fuchsia-500/40 bg-fuchsia-500/10 px-5 py-2 text-sm text-foreground transition hover:border-fuchsia-400/80"
                    >
                        Open dashboard
                        <ArrowUpRight class="h-4 w-4" />
                    </Link>
                    <a
                        href="https://laravel.com/docs"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-full border border-border px-5 py-2 text-sm text-muted-foreground transition hover:border-border/80 hover:text-foreground"
                    >
                        Docs
                        <ArrowUpRight class="h-4 w-4" />
                    </a>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-fuchsia-500/20 bg-card p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-fuchsia-400/70">
                            Curate
                        </p>
                        <h2 class="mt-3 text-lg font-semibold text-foreground">
                            Keep sources tight
                        </h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Add feeds once, refresh with intent, and keep the noise out of your
                            focus loop.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-fuchsia-500/20 bg-card p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-fuchsia-400/70">
                            Read
                        </p>
                        <h2 class="mt-3 text-lg font-semibold text-foreground">
                            Stay in flow
                        </h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            A lean story list keeps the latest posts close without breaking
                            your rhythm.
                        </p>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-3xl border border-fuchsia-500/20 bg-card p-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                        Quick links
                    </p>
                    <div class="mt-5 space-y-4 text-sm">
                        <a
                            href="https://laracasts.com"
                            target="_blank"
                            class="flex items-center justify-between rounded-xl border border-border px-4 py-3 text-muted-foreground transition hover:border-fuchsia-500/30 hover:text-foreground"
                        >
                            Laracasts
                            <ArrowUpRight class="h-4 w-4 text-fuchsia-400" />
                        </a>
                        <a
                            href="https://cloud.laravel.com"
                            target="_blank"
                            class="flex items-center justify-between rounded-xl border border-border px-4 py-3 text-muted-foreground transition hover:border-fuchsia-500/30 hover:text-foreground"
                        >
                            Laravel Cloud
                            <ArrowUpRight class="h-4 w-4 text-fuchsia-400" />
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-fuchsia-500/10 bg-gradient-to-br from-card via-card to-fuchsia-200/40 p-6 dark:to-fuchsia-950/40">
                    <p class="text-xs uppercase tracking-[0.35em] text-fuchsia-400/70">
                        Ready when you are
                    </p>
                    <p class="mt-3 text-sm text-muted-foreground">
                        Start with one feed and watch the board update on your schedule.
                    </p>
                </div>
            </aside>
        </main>
    </div>
</template>
