<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { RefreshCcw, Rss, Settings2, Trash2 } from 'lucide-vue-next';
import { onUnmounted, ref, watch } from 'vue';
import FeedController from '@/actions/App/Http/Controllers/FeedController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { show as feedShow, manage as feedManage } from '@/routes/feeds';
import { type BreadcrumbItem } from '@/types';

type FeedSummary = {
    id: number;
    title: string;
    url: string;
    site_url?: string | null;
    description?: string | null;
    last_fetched_at?: string | null;
    items_count: number;
};

type Props = {
    feeds: FeedSummary[];
    status?: string | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'All Stories',
        href: dashboard().url,
    },
    {
        title: 'Manage Feeds',
        href: feedManage().url,
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

watch(
    () => props.status,
    (status) => {
        if (status) {
            setStatusMessage(status);
        }
    },
    { immediate: true }
);

onUnmounted(() => {
    if (statusTimeoutId !== null) {
        clearTimeout(statusTimeoutId);
    }
});
</script>

<template>
    <Head title="Manage Feeds" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="rounded-2xl border border-fuchsia-500/20 bg-neutral-950 p-6">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg border border-fuchsia-500/40 bg-fuchsia-500/10 p-2">
                        <Settings2 class="h-5 w-5 text-fuchsia-200" />
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-fuchsia-300/70">Settings</p>
                        <h1 class="text-2xl font-semibold text-neutral-100">Manage Feeds</h1>
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

            <div class="grid gap-6 lg:grid-cols-[340px,1fr]">
                <Card class="border-fuchsia-500/20 bg-neutral-950">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-neutral-100">
                            <Rss class="h-4 w-4 text-fuchsia-300" />
                            Add a feed
                        </CardTitle>
                        <CardDescription class="text-neutral-400">
                            Paste an RSS or Atom URL to add a new source.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form v-bind="FeedController.store.form()" class="space-y-4" v-slot="{ errors, processing }">
                            <div class="grid gap-2">
                                <Label for="url" class="text-neutral-300">Feed URL</Label>
                                <Input
                                    id="url"
                                    name="url"
                                    type="url"
                                    placeholder="https://example.com/rss.xml"
                                    required
                                    class="border-fuchsia-500/20 bg-neutral-950 text-neutral-100 placeholder:text-neutral-500"
                                />
                                <InputError :message="errors.url" />
                                <InputError :message="errors.url_hash" />
                            </div>

                            <Button :disabled="processing">Add feed</Button>
                        </Form>
                    </CardContent>
                </Card>

                <Card class="border-fuchsia-500/20 bg-neutral-950">
                    <CardHeader>
                        <CardTitle class="text-neutral-100">Current feeds</CardTitle>
                        <CardDescription class="text-neutral-400">
                            Edit your source list and refresh ingest.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-if="feeds.length === 0"
                            class="rounded-lg border border-dashed border-fuchsia-500/20 p-4 text-sm text-neutral-400"
                        >
                            No feeds added yet.
                        </div>

                        <div
                            v-for="feed in feeds"
                            :key="feed.id"
                            class="rounded-xl border border-fuchsia-500/10 bg-neutral-900/60 p-4"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-2">
                                    <div class="space-y-1">
                                        <Link
                                            :href="feedShow(feed.id)"
                                            class="font-medium text-neutral-100 transition-colors hover:text-white"
                                        >
                                            {{ feed.title }}
                                        </Link>
                                        <p class="text-xs text-neutral-500">
                                            {{ feed.url }}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-neutral-400">
                                        <Badge variant="secondary">
                                            {{ feed.items_count }} items
                                        </Badge>
                                        <span v-if="feed.last_fetched_at">
                                            Updated {{ formatDate(feed.last_fetched_at) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Form
                                        v-bind="FeedController.refresh.form({ feed: feed.id })"
                                        :options="{ preserveScroll: true }"
                                    >
                                        <Button variant="secondary" size="icon" aria-label="Refresh feed">
                                            <RefreshCcw class="h-4 w-4" />
                                        </Button>
                                    </Form>
                                    <Form
                                        v-bind="FeedController.destroy.form({ feed: feed.id })"
                                        :options="{ preserveScroll: true }"
                                    >
                                        <Button variant="destructive" size="icon" aria-label="Remove feed">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </Form>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
