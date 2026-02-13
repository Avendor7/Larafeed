<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { show as feedItemShow } from '@/routes/feed-items';
import { type BreadcrumbItem } from '@/types';

type FeedDetail = {
    id: number;
    title: string;
    url: string;
    site_url?: string | null;
    description?: string | null;
    last_fetched_at?: string | null;
};

type FeedItemSummary = {
    id: number;
    title?: string | null;
    url?: string | null;
    summary?: string | null;
    published_at?: string | null;
};

type Props = {
    feed: FeedDetail;
    items: FeedItemSummary[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'All Stories',
        href: dashboard().url,
    },
    {
        title: props.feed.title,
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
    <Head :title="feed.title" />

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
                            Back to all stories
                        </Link>
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.35em] text-fuchsia-300/70">Feed timeline</p>
                            <h1 class="text-2xl font-semibold text-neutral-100 md:text-3xl">
                                {{ feed.title }}
                            </h1>
                            <p v-if="feed.description" class="text-sm text-neutral-400">
                                {{ feed.description }}
                            </p>
                            <p v-if="feed.last_fetched_at" class="text-xs text-neutral-500">
                                Updated {{ formatDate(feed.last_fetched_at) }}
                            </p>
                        </div>
                    </div>

                    <Button v-if="feed.site_url || feed.url" variant="secondary" as-child>
                        <a :href="feed.site_url ?? feed.url" target="_blank" rel="noreferrer">
                            <ExternalLink class="mr-2 h-4 w-4" />
                            Open source
                        </a>
                    </Button>
                </div>
            </div>

            <Card class="border-fuchsia-500/20 bg-neutral-950">
                <CardHeader>
                    <CardTitle class="text-neutral-100">Stories</CardTitle>
                    <CardDescription class="text-neutral-400">Ordered by published date, newest first.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-if="items.length === 0"
                        class="rounded-lg border border-dashed border-fuchsia-500/20 p-6 text-sm text-neutral-400"
                    >
                        No stories are available for this feed yet.
                    </div>

                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="rounded-2xl border border-fuchsia-500/10 bg-neutral-900/60 p-4"
                    >
                        <div class="space-y-1">
                            <Link
                                :href="feedItemShow(item.id)"
                                class="text-base font-semibold text-neutral-100 transition-colors hover:text-white"
                            >
                                {{ item.title ?? 'Untitled story' }}
                            </Link>
                            <p v-if="item.published_at" class="text-xs text-neutral-500">
                                {{ formatDate(item.published_at) }}
                            </p>
                        </div>
                        <p v-if="item.summary" class="mt-3 line-clamp-3 text-sm text-neutral-400">
                            {{ item.summary }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
