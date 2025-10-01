<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Logo from '@/Shared/Logo.vue';
import Background from '@/Shared/Background.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <Head title="MARKETMIS" />
  <Background>
    <template #logo>
      <div class="flex flex-col sm:justify-center items-center justify-center mb-6">
        <Logo class="fill-white" style="width: 200px; height: 100px;" />
      </div>

      <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
          {{ status }}
      </div>

      <form @submit.prevent="submit">
        <div>
          <InputLabel for="email" value="Email" />
          <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus autocomplete="username" />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div class="mt-4">
            <InputLabel for="password" value="Password" />
            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
            <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="block mt-4">
            <label class="flex items-center">
                <Checkbox v-model:checked="form.remember" name="remember" />
                <span class="ms-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>
        <div class="block my-4">
          <button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full bg-btn-gradient text-white font-bold uppercase px-5 py-2 rounded focus:outline-none shadow hover:bg-primary/70 transition-colors">Sign In</button>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-between items-center">
          <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"> Forgot your password?
          </Link>

          <Link v-if="canResetPassword" :href="route('register')" class="underline font-bold text-sm text-red-600 hover:text-red-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"> Not not yet register?
          </Link>
        </div>
      </form> 
    </template>
  
  </Background>
  <Footer />
</template>
