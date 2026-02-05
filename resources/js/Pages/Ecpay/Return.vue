<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    message: String,
});
</script>

<template>
    <Head title="返回商店" />

    <div class="min-h-screen bg-gray-50 dark:bg-black py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 返回商店卡片 -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 overflow-hidden">
                <!-- 頂部橫幅 -->
                <div class="bg-indigo-500 px-6 py-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">{{ message }}</h1>
                </div>

                <!-- 內容區域 -->
                <div class="p-6 text-center">
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        感謝您的購買！如有任何問題，歡迎聯繫我們的客服團隊。
                    </p>

                    <!-- 按鈕區域 -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a
                            href="/"
                            class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gray-800 dark:bg-white border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-100 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            返回首頁
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
