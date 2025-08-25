<script setup>
import { Head, Link } from '@inertiajs/vue3';
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
          <h1 class="text-2xl font-bold text-primary mb-4">Privacy Policy</h1>
            <p class="text-secondary mb-4">
                We value your privacy and are committed to protecting your personal information. This Privacy Policy outlines how we collect, use, and safeguard your data.
            </p>
            <ul class="list-disc list-inside text-gray-600 mb-4">
                <li>We collect personal information such as your name, email address, and contact details when you register or use our services.</li>
                <li>Your data is used to provide and improve our services, communicate with you, and comply with legal obligations.</li>
                <li> We implement appropriate security measures to protect your personal information from unauthorized access, disclosure, or misuse.</li>
                <li>We do not share your personal information with third parties without your consent, except           as required by law or to provide our services.</li>
                <li>You have the right to access, correct, or delete your personal information at any time.</li>
                <li>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date.</li>
            </ul>
            <p class="text-gray-600 mb-4">
                If you have any questions or concerns about our Privacy Policy, please contact us at <a href="mailto:" class="text-blue-400">marketchristian7@gmail.com</a>.
            </p>
            <p class="text-gray-600 mb-4">
                By using our services, you consent to the collection and use of your personal information as described in this Privacy Policy.
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
