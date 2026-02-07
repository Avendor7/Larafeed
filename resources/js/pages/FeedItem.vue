<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

type FeedItemDetail = {
    id: number;
    title?: string | null;
    url?: string | null;
    summary?: string | null;
    content?: string | null;
    published_at?: string | null;
    feed: {
        id: number;
        title: string;
    };
};

type Props = {
    item: FeedItemDetail;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: props.item.title ?? 'Story',
    },
];

const formatDate = (value?: string | null) => {
    if (!value) {
        return null;
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
};
</script>

<template>
    <Head :title="item.title ?? 'Story'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="rounded-2xl border border-fuchsia-500/20 bg-neutral-950 p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-2">
                        <Link
                            :href="dashboard()"
                            class="inline-flex items-center gap-2 text-sm font-medium text-neutral-400 transition-colors hover:text-neutral-100"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Back to dashboard
                        </Link>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-fuchsia-300/80">
                                {{ item.feed.title }}
                            </p>
                            <h1 class="text-2xl font-semibold text-neutral-100 md:text-3xl">
                                {{ item.title ?? 'Untitled story' }}
                            </h1>
                            <p v-if="item.published_at" class="text-sm text-neutral-400">
                                {{ formatDate(item.published_at) }}
                            </p>
                        </div>
                    </div>

                    <Button v-if="item.url" variant="secondary" as-child>
                        <a :href="item.url" target="_blank" rel="noreferrer">
                            <ExternalLink class="mr-2 h-4 w-4" />
                            Open original
                        </a>
                    </Button>
                </div>
            </div>

            <div class="rounded-2xl border border-fuchsia-500/10 bg-neutral-950/60 p-6">
                <div
                    v-if="item.content"
                    class="prose prose-invert max-w-none prose-a:text-fuchsia-200 prose-a:no-underline hover:prose-a:text-fuchsia-100"
                    v-html="item.content"
                ></div>

                <div v-else class="rounded-lg border border-dashed border-fuchsia-500/20 p-6">
                    <p class="text-sm text-neutral-400">
                        This feed item does not include full content. Showing the summary instead.
                    </p>
                    <p v-if="item.summary" class="mt-3 text-base text-neutral-100">
                        {{ item.summary }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
