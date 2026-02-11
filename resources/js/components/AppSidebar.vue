<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bookmark, Clock, LayoutGrid } from 'lucide-vue-next';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'All stories',
        href: dashboard({ query: { filter: 'all' } }),
        icon: LayoutGrid,
    },
    {
        title: 'Today',
        href: dashboard({ query: { filter: 'today' } }),
        icon: Clock,
    },
    {
        title: 'Bookmarks',
        href: dashboard({ query: { filter: 'bookmarks' } }),
        icon: Bookmark,
    },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="sidebar"
        class="border-sidebar-border bg-sidebar text-sidebar-foreground"
    >
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-lg font-semibold text-white"
                            >
                                R
                            </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
