<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ListTree } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { show as feedItemShow } from '@/routes/feed-items';
import { show as feedShow } from '@/routes/feeds';
import { type BreadcrumbItem } from '@/types';

type FeedItemSummary = {
    id: number;
    title?: string | null;
    url?: string | null;
    summary?: string | null;
    published_at?: string | null;
    feed: {
        id: number;
        title: string;
    };
};

type Props = {
    items: FeedItemSummary[];
    status?: string | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'All Stories',
        href: dashboard().url,
    },
];

const statusMessage = ref<string | null>(props.status ?? null);
let statusTimeoutId: ReturnType<typeof setTimeout> | null = null;

const setStatusMessage = (message?: string | null): void => {
    statusMessage.value = message ?? null;

    if (statusTimeoutId !== null) {
        clearTimeout(statusTimeoutId);
        statusTimeoutId = null;
    }

    if (statusMessage.value) {
        statusTimeoutId = setTimeout(() => {
            statusMessage.value = null;
            statusTimeoutId = null;
        }, 10000);
    }
};

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

const reloadStories = (): void => {
    router.reload({
        only: ['items'],
        preserveScroll: true,
        preserveState: true,
    });
};

let pollIntervalId: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    setStatusMessage(props.status);

    pollIntervalId = setInterval(() => {
        if (document.visibilityState === 'visible') {
            reloadStories();
        }
    }, 5000);
});

watch(
    () => props.status,
    (status) => {
        if (status) {
            setStatusMessage(status);
        }
    }
);

onUnmounted(() => {
    if (pollIntervalId !== null) {
        clearInterval(pollIntervalId);
    }

    if (statusTimeoutId !== null) {
        clearTimeout(statusTimeoutId);
    }
});
</script>

<template>
    <Head title="All Stories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="rounded-2xl border border-fuchsia-500/20 bg-neutral-950 p-6">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg border border-fuchsia-500/40 bg-fuchsia-500/10 p-2">
                        <ListTree class="h-5 w-5 text-fuchsia-200" />
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-fuchsia-300/70">Reader</p>
                        <h1 class="text-2xl font-semibold text-neutral-100">All Stories</h1>
                    </div>
                </div>
            </div>

            <Card
                v-if="statusMessage"
                class="border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100"
            >
                <CardHeader class="space-y-1">
                    <CardTitle class="text-base">Status</CardTitle>
                    <CardDescription class="text-emerald-900/80 dark:text-emerald-100/80">
                        {{ statusMessage }}
                    </CardDescription>
                </CardHeader>
            </Card>

            <Card class="border-fuchsia-500/20 bg-neutral-950">
                <CardHeader>
                    <CardTitle class="text-neutral-100">Latest stories</CardTitle>
                    <CardDescription class="text-neutral-400">
                        Fresh entries from all feeds, ordered by publish time.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-if="items.length === 0"
                        class="rounded-lg border border-dashed border-fuchsia-500/20 p-6 text-sm text-neutral-400"
                    >
                        Add feeds from Manage Feeds to start seeing stories here.
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
                            <p class="text-xs text-neutral-500">
                                <Link :href="feedShow(item.feed.id)" class="transition-colors hover:text-neutral-300">
                                    {{ item.feed.title }}
                                </Link>
                                <span v-if="item.published_at">
                                    · {{ formatDate(item.published_at) }}
                                </span>
                            </p>
                        </div>
                        <p v-if="item.summary" class="mt-3 line-clamp-2 text-sm text-neutral-400">
                            {{ item.summary }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
