<!-- Any Inertia page or normal Vue route -->
<template>
    <AppLayout title="Stalls">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <h1 class="mb-8 text-3xl font-bold text-primary">{{`Welcome, ${user?.name}!`}}</h1>
          <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
            
          </div>
           <div class="grid grid-cols-1">
            <div class="bg-white rounded-2xl shadow p-4 flex items-center space-x-4">
                <div class="bg-green-100 text-green-600 p-2 rounded-full">
                    <!-- Green Shield Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3l8 4v5c0 5.25-3.25 9.75-8 11-4.75-1.25-8-5.75-8-11V7l8-4z" />
                    </svg>
                </div>
                <div class="flex items-center justify-between w-full">
                    <span class="text-2xl font-semibold text-gray-600">Here's your application overview <Badge color="green">Valid</Badge> </span>
                    <Link :href="route('applications.stalls.index')">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                    </Link>
                  
                </div>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 my-5">
            <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
                <div class="bg-orange-100 text-orange-600 p-3 rounded-full">
                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="4" width="24" height="24" rx="3" fill="none" stroke="currentColor" stroke-width="2"/>
                        <rect x="8" y="8" width="16" height="2" fill="currentColor"/>
                        <rect x="8" y="12" width="12" height="2" fill="currentColor"/>
                        <rect x="8" y="16" width="14" height="2" fill="currentColor"/>
                        <rect x="8" y="20" width="10" height="2" fill="currentColor"/>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-bold">Total Applications</p>
                    <p class="text-2xl font-bold text-orange-600">{{myApplicationCount?.total_applications}}</p>
                </div>
            </div>

            <!-- Stall Rentals -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
                <div class="bg-purple-100 text-purple-600 p-3 rounded-full">
                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="20" width="28" height="8" rx="1" fill="none" stroke="currentColor" stroke-width="2"/>
                        <rect x="4" y="12" width="4" height="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <rect x="8" y="12" width="4" height="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <rect x="12" y="12" width="4" height="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <rect x="16" y="12" width="4" height="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <rect x="20" y="12" width="4" height="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <rect x="24" y="12" width="4" height="10" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-bold">Stall Rentals</p>
                    <p class="text-2xl font-bold text-purple-600">{{myApplicationCount?.stall_rentals}}</p>
                </div>
            </div>

            <!-- Volante -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
                <div class="bg-green-100 text-green-600 p-3 rounded-full">
                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <rect x="6" y="2" width="20" height="28" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="10" r="2.5" fill="currentColor"/>
                        <rect x="16" y="9" width="8" height="1.5" fill="currentColor"/>
                        <rect x="10" y="14" width="12" height="1.5" fill="currentColor"/>
                        <rect x="10" y="17" width="14" height="1.5" fill="currentColor"/>
                        <rect x="10" y="20" width="10" height="1.5" fill="currentColor"/>
                        <polygon points="20,24 22,22 24,24 22,26" fill="currentColor"/>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-bold">Volante Permits</p>
                    <p class="text-2xl font-bold text-green-600">{{myApplicationCount?.volante_permits}}</p>
                </div>
            </div>

            <!-- Volante -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
                <div class="bg-green-100 text-green-600 p-3 rounded-full">
                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="12" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M 16 8 L 16 16 L 22 22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="24" cy="8" r="4" fill="#ff6b35"/>
                        <text x="24" y="11" text-anchor="middle" font-family="Arial" font-size="8" font-weight="bold" fill="white">!</text>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-bold">Pending Review</p>
                    <p class="text-2xl font-bold text-green-600">{{myApplicationCount?.pending_permits}}</p>
                </div>
            </div>


          </div>
          <h2 class="mb-8 text-3xl font-bold text-primary">Recent Applications</h2>
           <BaseTable :headers="headers" :data="myTopApply">
                <!-- Optional: Customize rows -->
                <template #row = "{ data }">
                    <tr v-for="(stall, i) in data" :key="stall.type" class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ stall.type }}</td>
                        <td class="px-4 py-3 capitalize">
                            <div>
                                <div class="font-semibold">{{ stall.data.business_name }}</div>
                                <div class="text-xs text-gray-500">{{ stall.stall }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 capitalize">
                             <Badge :color="status(stall.status).color">
                                {{ status(stall.status).status }}
                             </Badge>
                        </td>
                        
                    </tr>
                    <tr v-if="data?.length === 0">
                        <td class="px-6 py-4 border-t text-center" colspan="7">No records found.</td>
                    </tr>
                </template>
            </BaseTable>
        </div>
    </AppLayout>
</template>


<script setup>
import { defineProps } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Shared/Badge.vue';
import BaseTable from '@/Shared/BaseTable.vue';
import { data } from 'autoprefixer';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    myTopApply: Array,
    myApplicationCount: Object,
});

const headers = [
    'Type',
    'Data',
    'Status'
];
//0-pending,1- approved, 2-rejected, 3- expired
const status = (stat) => {
    let statuss = {
        color: 'blue',
        status: 'Vacant'
    };
    switch (stat) {
        case 3:
            statuss = {
                color: 'orange',
                status: 'Expired'
            };
            break;
        case 2:
            statuss = {
                color: 'red',
                status: 'Rejected'
            };
            break;
        case 1:
            statuss = {
                color: 'green',
                status: 'Approved'
            };
            break;
        default:
            statuss = {
                color: 'blue',
                status: 'Pending'
            };
    }
   
    return statuss;
};
</script>

