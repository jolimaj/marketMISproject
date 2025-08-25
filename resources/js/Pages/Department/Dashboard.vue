<!-- Any Inertia page or normal Vue route -->
<template>
    <AppLayout title="Stalls">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <h1 class="mb-8 text-3xl font-bold text-primary">{{`Welcome, ${user?.name} to ${user?.subAdminType?.departments?.name}`}}</h1>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 py-5">
            <!-- Volante -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
                <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-bold">Pending Volante Permits</p>
                    <p class="text-2xl font-bold text-indigo-600">{{counts?.totalVolante}}</p>
                </div>
            </div>

            <!-- Stall Rentals -->
            <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
                <div class="bg-purple-100 text-purple-600 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M4 6h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm font-bold">Pending Stall Rental Permits</p>
                    <p class="text-2xl font-bold text-indigo-600">{{counts?.totalStall}}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
            <div class="bg-green-100 text-green-600 p-3 rounded-full">
                <svg width="24" height="24" viewBox="0 0 24 24" aria-label="Approve" role="img"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M8 12l2.5 2.5L16 9.5"></path>
                </svg>

            </div>
            <div>
                <p class="text-gray-600 text-sm font-bold">Approved This Month</p>
                    <p class="text-2xl font-bold text-indigo-600">{{counts?.total?.approved ?? 0}}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 flex items-center space-x-4">
            <div class="bg-red-100 text-red-600 p-3 rounded-full">
                <svg width="24" height="24" viewBox="0 0 24 24" aria-label="Reject" role="img"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M9 9l6 6M15 9l-6 6"></path>
                </svg>

            </div>
            <div>
                <p class="text-gray-600 text-sm font-bold">Rejected This Month</p>
                <p class="text-2xl font-bold text-indigo-600">{{counts?.total?.rejected ?? 0}}</p>
            </div>
        </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-1 py-5">
            <h2 class="mb-8 text-3xl font-bold text-primary">Recent Applications</h2>
            <BaseTable :headers="headers" :data="myTopApply">
                <!-- Optional: Customize rows -->
                <template #row = "{ data }">
                    <tr v-for="(stall, i) in data" :key="stall?.type" class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ stall?.type }}</td>
                        <td class="px-4 py-3 capitalize">
                            <div>
                                <div class="font-semibold">{{ stall?.data?.business_name }}</div>
                                <div class="text-xs text-gray-500">{{ stall?.stall }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 capitalize">
                             <Badge :color="status(stall?.status).color">
                                {{ status(stall?.status).status }}
                             </Badge>
                        </td>
                        
                    </tr>
                    <tr v-if="data?.length === 0">
                        <td class="px-6 py-4 border-t text-center" colspan="7">No records found.</td>
                    </tr>
                </template>
            </BaseTable>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-1 py-5">
            <Map :datas="stalls"/>
          </div>
    </div>
    </AppLayout>              
</template>


<script setup>
import { defineProps } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue'
import BaseTable from '@/Shared/BaseTable.vue';
import Map from '@/Components/Map.vue';
import Badge from '@/Shared/Badge.vue';

const headers = [
    'Type',
    'Data',
    'Status'
];

const props = defineProps({
    counts: Object,
    myTopApply: Object,
    stalls: Object,
    user: Object,
});

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
