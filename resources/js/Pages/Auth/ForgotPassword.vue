<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Logo from '@/Shared/Logo.vue';
import Background from '@/Shared/Background.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Footer from '@/Components/Footer.vue';


defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
  <Head title="MARKETIS" />
  <Background>
    <template #logo>
      <div class="flex flex-col sm:justify-center items-center justify-center mb-6">
        <Logo class="fill-white" style="width: 200px; height: 100px;" />
      </div>

      <div class="mb-4 text-sm text-gray-600">
          Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
      </div>

      <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
          {{ status }}
      </div>

      <form @submit.prevent="submit">
        <div>
          <InputLabel for="email" value="Email" />
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

        <div class="flex items-center justify-end mt-4">
          <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              Email Password Reset Link
          </PrimaryButton>
        </div>
      </form>
    </template>
  
  </Background>
  <Footer />
</template>
