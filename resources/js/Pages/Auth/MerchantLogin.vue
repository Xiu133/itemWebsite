<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('merchant.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="商家登入" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-6 text-center">
            <h2 class="text-xl font-semibold text-gray-800">商家登入</h2>
            <p class="text-sm text-gray-500 mt-1">管理您的商品與訂單</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="電子郵件" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="密碼" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">記住我</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    登入
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 text-center">
            <span class="text-sm text-gray-600">還沒有商家帳號？</span>
            <Link :href="route('merchant.register')" class="text-sm text-indigo-600 hover:text-indigo-900 underline ms-1">
                立即註冊
            </Link>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200 text-center">
            <Link :href="route('login')" class="text-sm text-gray-500 hover:text-gray-700">
                ← 一般用戶登入
            </Link>
        </div>
    </AuthenticationCard>
</template>
