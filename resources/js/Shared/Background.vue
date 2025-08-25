<template>
    <div class="relative w-full h-screen bg-cover bg-center flex items-center justify-center text-primary">
       <div class="absolute inset-0 bg-cover bg-center z-0" :style="{ backgroundImage: 'url(/images/bg.jpg)' }"></div>

        <!-- Blue Overlay (darker for better contrast) -->
        <div class="absolute inset-0 bg-primary/60 z-10"></div>
        <div class="relative z-20 w-full max-w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-y-auto sm:rounded-lg"
            style="max-height: 90vh;">
            <div><slot name="logo" /></div>
            <div><slot name="status" /></div>
            <div><slot name="forms" /></div>
        </div>

    </div>
</template>
