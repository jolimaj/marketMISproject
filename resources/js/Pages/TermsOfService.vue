<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Footer from '@/Components/Footer.vue';
import Background from '@/Shared/Background.vue';
import Logo from '@/Shared/Logo.vue';

defineProps({
    terms: String,
});
</script>

<template>
  <Background>
      <template #logo>
        <div class="flex flex-col sm:justify-center items-center justify-center mb-6">
          <Logo class="fill-white" style="width: 200px; height: 100px;" />
        </div>
      </template>

      <template #forms>
        <div class="flex flex-col items-center justify-center">
          <h1 class="text-2xl font-bold text-primary mb-4">Terms of Service</h1>
          <p class="text-secondary mb-4">
              By using this application, you agree to the following terms and conditions:
          </p>
          <ul class="list-disc list-inside text-gray-600 mb-4">
              <li>You must be at least 18 years old to use this application.</li>
              <li>You agree to provide accurate and complete information during registration.</li>            
              <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
              <li>You agree not to use the application for any illegal or unauthorized purposes.</li>
              <li>We reserve the right to modify or terminate the application at any time without notice
              </li>
              <li>We are not liable for any damages or losses resulting from your use of the application.</li>
          </ul>
          <p class="text-gray-600 mb-4">
              If you do not agree to these terms, please do not use the application.
          </p>
          <p class="text-gray-600 mb-4">
              For more information, please contact us at <a href="mailto:" class="text-blue-400">marketchristian7@gmail.com</a>.
          </p>
          <p class="text-gray-600 mb-4">
              By clicking "Accept", you agree to the terms and conditions outlined above.
          </p>
          <div class="mt-4 flex flex-col md:flex-row gap-4">
            <Link :href="route('register')" class="bg-primary text-white font-bold py-2 px-4 rounded hover:bg-primary-700 transition-colors">
                Accept
            </Link>   
            <Link :href="route('register')" class="bg-secondary text-white font-bold py-2 px-4 rounded hover:bg-secondary-700 transition-colors">
                Decline
            </Link>
          </div>  
        </div>
      </template>
  </Background>
  <Footer />
</template>
