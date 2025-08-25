<script setup>
import { Link } from '@inertiajs/vue3';
import Logo from '@/Shared/Logo.vue';
import Icon from '@/Shared/Icons.vue';
import { defineProps, ref, reactive } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});


const isSidebarOpen = ref(false)
const activeFaqs = ref(null)

function closeSidebar() {
  isSidebarOpen.value = false
}

function togglefaqs(index) {
  activeFaqs.value = activeFaqs.value === index ? null : index
}

const faqsStep = [
    {
    icon: "fill-out",
    title: 'Fill Out Online Form',
    description: 'Complete our simple online form to begin your application process.',
  },
  {
    icon: 'submit-doc',
    title: 'Submit Documents',
    description: 'Upload all required documents like business IDs and permits.',
  },
  {
    icon: 'wait',
    title: 'Wait for Approval',
    description: 'We’ll review your submission and notify you once it’s approved.',
  },
  {
    icon: 'confirm',
    title: 'Receive Confirmation',
    description: 'Get an email confirmation and download your approved documents.',
  },
];

const form = reactive({
  name: '',
  email: '',
  message: '',
})

const submitForm = () => {
  // Replace this with actual form logic (e.g., Inertia POST or axios)
  console.log('Contact Form Submitted:', form)
  alert('Thank you for your message!')
  form.name = ''
  form.email = ''
  form.message = ''
}
</script>

<style scoped>
.clip-polygon {
  clip-path: polygon(50% 0%, 100% 0%, 100% 100%, 0% 100%);
}
</style>
<template>
    <div class="bg-white-50 text-black/50 dark:bg-white dark:text-white/50 font-montserrat">
        <div class="relative min-h-screen flex flex-col items-center justify-between selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl lg:max-w-7xl">
                <header class="sticky top-0 z-50 bg-white shadow-md">
                    <div class="w-full px-4 py-4 flex items-center justify-between">
                    
                    <!-- Logo -->
                    <div class="flex justify-start">
                        <Logo class="fill-white" style="width: 130px; height: 55px;" />
                    </div>

                    <!-- Desktop Nav -->
                    <nav class="hidden lg:flex gap-4 items-center">
                        <Link href="/" class="rounded-md px-3 py-2 font-semibold text-primary hover:bg-primary/90 hover:text-white  hover:font-bold transition">HOME</Link>
                        <Link href="#about" class="rounded-md px-3 py-2 font-semibold text-primary hover:bg-primary/90 hover:text-white  hover:font-bold transition">ABOUT</Link>
                        <Link href="#services" class="rounded-md px-3 py-2 font-semibold text-primary hover:bg-primary/90 hover:text-white  hover:font-bold transition">SERVICES</Link>
                        <Link href="#faqs" class="rounded-md px-3 py-2 font-semibold text-primary hover:bg-primary/90 hover:text-white  hover:font-bold transition">FAQS</Link>
                        <Link href="#contact" class="rounded-md px-3 py-2 font-semibold text-primary hover:bg-primary/90 hover:text-white  hover:font-bold transition">CONTACT</Link>
                        <Link :href="route('login')" class="bg-btn-gradient text-white font-bold uppercase rounded-full px-4 py-2  hover:bg-secondary transition cursor-pointer">Sign in</Link>
                    </nav>

                    <!-- Hamburger (Mobile) -->
                    <button @click="isSidebarOpen = true" class="lg:hidden">
                        <Icon name="hamburger" class="w-6 h-6 text-primary" />
                    </button>
                    </div>
                </header>

                <!-- Sidebar Overlay -->
                <div
                    v-show="isSidebarOpen"
                    @click="closeSidebar"
                    class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity"
                ></div>

                <!-- Sidebar Menu -->
                <aside
                    class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-50 transform transition-transform duration-300 lg:hidden"
                    :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                >
                    <div class="flex items-center justify-between px-4 py-4 border-b">
                    <span class="font-bold text-lg">Menu</span>
                    <button @click="closeSidebar">
                        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    </div>

                    <nav class="flex flex-col px-4 py-6 space-y-2">
                    <Link href="#" @click="closeSidebar" class="font-semibold text-primary hover:bg-primary hover:text-white px-3 py-2 rounded transition">HOME</Link>
                    <Link href="#about" @click="closeSidebar" class="font-semibold text-primary hover:bg-primary hover:text-white px-3 py-2 rounded transition">ABOUT</Link>
                    <Link href="#services" @click="closeSidebar" class="font-semibold text-primary hover:bg-primary hover:text-white px-3 py-2 rounded transition">SERVICES</Link>
                    <Link href="#faqs" @click="closeSidebar" class="font-semibold text-primary hover:bg-primary hover:text-white px-3 py-2 rounded transition">FAQS</Link>
                    <Link href="#contact" @click="closeSidebar" class="font-semibold text-primary hover:bg-primary hover:text-white px-3 py-2 rounded transition">CONTACT</Link>
                    <Link :href="route('login')" @click="closeSidebar" class="bg-btn-gradient font-semibold text-white rounded px-3 py-2 text-center hover:bg-secondary transition cursor-pointer">Sign in</Link>
                    </nav>
                </aside>


                <section id="/" class="relative w-full h-screen bg-cover bg-center flex items-center justify-center text-center">
                    <!-- Background Image -->
                    <div class="absolute inset-0 bg-cover bg-center z-0" :style="{ backgroundImage: 'url(/images/bg.jpg)' }"></div>

                    <!-- Blue Overlay (darker for better contrast) -->
                    <div class="absolute inset-0 bg-primary/60 z-10"></div>

                    <!-- Text Content -->
                    <div class="relative z-20 px-6">
                        <h1 class="text-4xl md:text-6xl font-extrabold text-white drop-shadow-md mb-4">
                            Empowering Local Businesses
                        </h1>
                        <p class="text-lg md:text-xl text-white max-w-xl mx-auto drop-shadow-sm">
                            "Apply for Permits & Rentals – Fast, Simple, Online."
                        </p>
                    </div>
                </section>
                <section id="about" class="py-20 bg-white text-gray-800">
                <div class="container mx-auto px-6 md:px-12 flex flex-col md:flex-row items-center gap-12">
                    
                    <!-- Text Content -->
                    <div class="flex-1">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4 text-primary">
                        About <span class="text-secondary">MarketIS</span>
                    </h2>
                    <p class="text-lg text-gray-600 mb-6">
                        MarketIS is your all-in-one digital portal for stall rentals, volantes, and business permit applications. We simplify government processes so you can focus on growing your business.
                    </p>

                    <!-- Hoverable Feature Highlights -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg border hover:shadow-lg transition group cursor-pointer">
                        <h4 class="text-xl font-semibold text-primary group-hover:underline">Fast & Paperless</h4>
                        <p class="text-sm text-gray-600">Submit applications online without visiting the office.</p>
                        </div>
                        <div class="p-4 rounded-lg border hover:shadow-lg transition group cursor-pointer">
                        <h4 class="text-xl font-semibold text-primary group-hover:underline">Local Business Support</h4>
                        <p class="text-sm text-gray-600">Built to empower community entrepreneurs and vendors.</p>
                        </div>
                    </div>
                    </div>

                    <!-- Optional Image or Illustration -->
                    <div class="flex-1">
                    <img
                        src="/images/business.jpg"
                        alt="About MarketIS"
                        class="w-full max-w-md mx-auto hover:scale-105 transition-transform duration-300"
                    />
                    </div>
                </div>
                </section>

                <section id="services" class="py-16 bg-gray-50 text-center">
                    <div class="container mx-auto px-6">
                        <h2 class="text-3xl md:text-4xl font-bold text-primary mb-12">Our Services</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <!-- Business Permit Application -->
                            <!-- <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition p-8 group cursor-pointer hover:bg-primary/10">
                                <div class="flex justify-center mb-4">
                                    <svg class="w-12 h-12 text-primary group-hover:scale-110 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-3-3v6m-7 6h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4.586a1 1 0 0 1-.707-.293l-1.414-1.414A1 1 0 0 0 12.586 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-primary mb-2">Business Permit Application</h3>
                                <p class="text-gray-600 text-sm">
                                    Submit your business permit application entirely online. Fast, compliant, and paperless.
                                </p>
                            </div> -->
                             <!-- Stall Rental -->
                            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition p-8 group cursor-pointer hover:bg-primary/10">
                                <div class="flex justify-center mb-4">
                                    <svg class="w-12 h-12 text-primary group-hover:scale-110 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M3 10h18M5 10v10h14V10M9 21h6" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-primary mb-2">Stall Rental</h3>
                                <p class="text-gray-600 text-sm">
                                    Secure a market stall location for business or seasonal selling. Quick and convenient.
                                </p>
                            </div>

                            <!-- Volante Rental -->
                            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition p-8 group cursor-pointer hover:bg-primary/10">
                                <div class="flex justify-center mb-4">
                                    <svg class="w-12 h-12 text-primary group-hover:scale-110 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-primary mb-2">Volante Rental</h3>
                                <p class="text-gray-600 text-sm">
                                    Rent promotional display volantes for your events, campaigns, or kiosks with ease.
                                </p>
                            </div>

                           

                        </div>
                    </div>
                </section>

                <section id="faqs" class="py-20 bg-gray-50 text-gray-800">
                    <div class="max-w-3xl mx-auto px-6">
                    <h2 class="text-4xl font-bold text-center mb-10 text-primary">How It Works</h2>

                    <div v-for="(step, index) in faqsStep" :key="index" class="mb-4 border rounded-lg overflow-hidden">
                        <button
                        @click="togglefaqs(index)"
                        class="w-full flex items-center justify-between px-6 py-4 bg-white hover:bg-secondary/90 hover:text-white transition text-left"
                        >
                        <div class="flex items-center gap-3 hover:text-white transition">
                            <span class="text-xl"><Icon :name="step.icon" class="w-6 h-6 text-primary" /></span>
                            <span class="font-semibold text-primary">{{ step.title }}</span>
                        </div>
                        <svg
                            :class="{ 'rotate-180': activeFaqs === index }"
                            class="w-5 h-5 transform transition-transform duration-300 text-primary"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                        </svg>
                        </button>

                        <div
                        v-show="activeFaqs === index"
                        class="px-6 pb-4 text-sm text-gray-600 bg-white transition-all duration-300"
                        >
                        {{ step.description }}
                        </div>
                    </div>
                    </div>
                </section>

                <section id="contact" class="py-20 bg-btn-gradient/90 text-primary">
                    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-start">
                    
                    <!-- Contact Details -->
                    <div>
                        <h2 class="text-4xl font-bold mb-4">Get in Touch</h2>
                        <p class="text-lg text-gray-600 mb-6">
                        Reach out to us for inquiries about stall rentals, permit processing, or assistance.
                        </p>
                        <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0 0 16 4H4a2 2 0 0 0-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.118z" />
                            </svg>
                            </span>
                            <span>marketchristian7@gmail.com</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h13A1.5 1.5 0 0 1 18 3.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 2 16.5v-13ZM4 4v12h12V4H4Z" />
                            </svg>
                            </span>
                            <span>(+63) 912-345-6789</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 3.636a7.5 7.5 0 0 1 9.9 0L17 5.586l-7 7-7-7L5.05 3.636Z" clip-rule="evenodd" />
                            </svg>
                            </span>
                            <span>Sariaya Municipal Hall, Quezon, Philippines</span>
                        </li>
                        </ul>
                    </div>

                    <!-- Contact Form -->
                    <form @submit.prevent="submitForm" class="space-y-6 w-full">
                        <div>
                        <label class="block text-sm font-bold">Your Name</label>
                        <input v-model="form.name" type="text" required
                                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring focus:ring-blue-200"/>
                        </div>
                        <div>
                        <label class="block text-sm font-bold">Email Address</label>
                        <input v-model="form.email" type="email" required
                                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring focus:ring-blue-200"/>
                        </div>
                        <div>
                        <label class="block text-sm font-bold">Message</label>
                        <textarea v-model="form.message" rows="4" required
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring focus:ring-blue-200"></textarea>
                        </div>
                        <button
                        type="submit"
                        class="bg-btn-gradient text-white font-bold uppercase rounded-full px-4 py-2 hover:bg-secondary transition"
                        >
                        Send Message
                        </button>
                    </form>
                    </div>
                </section>
                <footer class="py-5 font-bold text-center text-sm text-white dark:text-white bg-btn-gradient">
                    MarketIS © 2025. All rights reserved.
                </footer>
            </div>
        </div>
    </div>
</template>

