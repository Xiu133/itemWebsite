<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    order_number: String,
    message: String,
    order: Object,
    payment: Object,
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('zh-TW', {
        style: 'currency',
        currency: 'TWD',
        minimumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <Head title="付款處理中" />

    <div class="min-h-screen bg-gray-50 dark:bg-black py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 處理中狀態卡片 -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 overflow-hidden">
                <!-- 頂部狀態橫幅 -->
                <div class="bg-yellow-500 px-6 py-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                        <svg class="w-10 h-10 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">付款處理中</h1>
                    <p class="text-yellow-100 mt-2">{{ message }}</p>
                </div>

                <!-- 訂單資訊 -->
                <div class="p-6">
                    <!-- 提示訊息 -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    您的付款正在處理中，請稍後再查看訂單狀態。如有任何問題，請聯繫客服。
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 付款資訊 -->
                    <div class="border-b border-gray-200 dark:border-zinc-700 pb-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">訂單資訊</h2>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">訂單編號</dt>
                                <dd class="font-medium text-gray-900 dark:text-white mt-1">{{ order_number }}</dd>
                            </div>
                            <div v-if="payment">
                                <dt class="text-gray-500 dark:text-gray-400">交易編號</dt>
                                <dd class="font-medium text-gray-900 dark:text-white mt-1">{{ payment.trade_no }}</dd>
                            </div>
                            <div v-if="order?.created_at">
                                <dt class="text-gray-500 dark:text-gray-400">訂單時間</dt>
                                <dd class="font-medium text-gray-900 dark:text-white mt-1">{{ order.created_at }}</dd>
                            </div>
                            <div v-if="payment">
                                <dt class="text-gray-500 dark:text-gray-400">付款狀態</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        處理中
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- 訂單明細 -->
                    <div v-if="order?.items?.length" class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">訂單明細</h2>
                        <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-zinc-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-medium">商品</th>
                                        <th class="px-4 py-3 text-center text-gray-600 dark:text-gray-300 font-medium">數量</th>
                                        <th class="px-4 py-3 text-right text-gray-600 dark:text-gray-300 font-medium">單價</th>
                                        <th class="px-4 py-3 text-right text-gray-600 dark:text-gray-300 font-medium">小計</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                    <tr v-for="(item, index) in order.items" :key="index">
                                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ item.name }}</td>
                                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ item.quantity }}</td>
                                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ formatPrice(item.price) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-medium">{{ formatPrice(item.subtotal) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-100 dark:bg-zinc-700">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">總計</td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white text-lg">{{ formatPrice(order.total) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- 按鈕區域 -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
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
