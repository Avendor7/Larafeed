<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { RefreshCcw, Rss, Trash2 } from 'lucide-vue-next';
import FeedController from '@/actions/App/Http/Controllers/FeedController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { show as feedItemShow } from '@/routes/feed-items';
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
    feeds: FeedSummary[];
    items: FeedItemSummary[];
    status?: string | null;
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
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
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <Card
                v-if="status"
                class="border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100"
            >
                <CardHeader class="space-y-1">
                    <CardTitle class="text-base">Status</CardTitle>
                    <CardDescription class="text-emerald-900/80 dark:text-emerald-100/80">
                        {{ status }}
                    </CardDescription>
                </CardHeader>
            </Card>

            <div class="grid gap-6 lg:grid-cols-[340px,1fr]">
                <div class="flex flex-col gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Rss class="h-4 w-4 text-amber-500" />
                                Add a feed
                            </CardTitle>
                            <CardDescription>
                                Paste an RSS or Atom feed URL to start collecting stories.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                v-bind="FeedController.store.form()"
                                class="space-y-4"
                                v-slot="{ errors, processing }"
                            >
                                <div class="grid gap-2">
                                    <Label for="url">Feed URL</Label>
                                    <Input
                                        id="url"
                                        name="url"
                                        type="url"
                                        placeholder="https://example.com/rss.xml"
                                        required
                                    />
                                    <InputError :message="errors.url" />
                                    <InputError :message="errors.url_hash" />
                                </div>

                                <Button :disabled="processing">Add feed</Button>
                            </Form>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>My feeds</CardTitle>
                            <CardDescription>
                                Keep your sources organized and refreshed.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div
                                v-if="feeds.length === 0"
                                class="rounded-lg border border-dashed border-muted-foreground/30 p-4 text-sm text-muted-foreground"
                            >
                                Add your first feed to see it listed here.
                            </div>

                            <div
                                v-for="feed in feeds"
                                :key="feed.id"
                                class="rounded-lg border border-border/60 p-4"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="space-y-1">
                                            <p class="font-medium">{{ feed.title }}</p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ feed.url }}
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
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

                <Card class="flex h-full flex-col">
                    <CardHeader>
                        <CardTitle>Latest stories</CardTitle>
                        <CardDescription>
                            Fresh entries from all your feeds, ready to read.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div
                            v-if="items.length === 0"
                            class="rounded-lg border border-dashed border-muted-foreground/30 p-6 text-sm text-muted-foreground"
                        >
                            Stories will appear here after your first feed refresh completes.
                        </div>

                        <div v-for="item in items" :key="item.id" class="space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <Link
                                        :href="feedItemShow(item.id)"
                                        class="text-base font-semibold text-foreground transition-colors hover:text-foreground/80"
                                    >
                                        {{ item.title ?? 'Untitled story' }}
                                    </Link>
                                    <p class="text-xs text-muted-foreground">
                                        {{ item.feed.title }}
                                        <span v-if="item.published_at">
                                            · {{ formatDate(item.published_at) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <p
                                v-if="item.summary"
                                class="text-sm text-muted-foreground line-clamp-2"
                            >
                                {{ item.summary }}
                            </p>
                            <div class="h-px w-full bg-border/60"></div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
