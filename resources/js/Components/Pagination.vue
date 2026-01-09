<template>
    <nav v-if="links.length > 0" class="flex justify-center mt-4 px-4">
        <ul class="inline-flex items-center space-x-2">
            <li v-for="(link, index) in links" :key="index">
                <Link v-if="link.url" :href="relativeUrl(link.url)" v-html="link.label" :class="[
                    'px-3 py-1 border rounded',
                    link.active ? 'bg-blue-500 text-white' : 'bg-white text-blue-500'
                ]" />
                <span v-else v-html="link.label" class="px-3 py-1 border rounded bg-gray-200 text-gray-500"></span>
            </li>
        </ul>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
});

const relativeUrl = (url) => {
    if (!url) return null;
    const a = document.createElement('a');
    a.href = url;
    return a.pathname + a.search;
};
</script>