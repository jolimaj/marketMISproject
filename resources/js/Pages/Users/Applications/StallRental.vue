<!-- Any Inertia page or normal Vue route -->
<template>
    <AppLayout title="Stalls">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stall
            </h2>
        </template>
        <h1 class="mb-8 text-3xl font-bold text-primary">Stall Rentals</h1>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-end mb-6 md:justify-self-center w-full">
            <Link type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                :href="`/my-applications/stall-rental-permits/create`" class="bg-btn-gradient text-white font-bold uppercase px-5 py-2 rounded focus:outline-none shadow hover:bg-primary-700 transition-colors">Apply for New Permit</Link>
            </div>
            <BaseTable :headers="headers" :data="stallRentals?.data">
                <template #row="{ data }">
                    <tr v-for="(bsn, i) in data" :key="stallRentals.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 capitalize">{{ bsn.name }}</td>
                        <td class="px-4 py-3 capitalize">{{ bsn.stalls.name }}</td>
                        <td class="px-4 py-3 capitalize">{{ bsn.stalls?.stallsCategories?.name }}</td>
                        <td class="px-4 py-3 capitalize">{{ bsn?.start_date ? `${formatDate(bsn.start_date)}` : '-'}}</td>
                        <td class="px-4 py-3 capitalize">
                             <Badge :color="status(bsn.permits.status).color">
                                {{ status(bsn.permits.status).status }}
                             </Badge>
                        </td>
                        <td class="px-4 py-3 capitalize">
                            <Link class="flex items-center px-4" :href="`/my-applications/stall-rental-permits/${bsn.id}/edit`" tabindex="-1" v-if="bsn.status === 1">
                                <icon name="download" class="block w-6 h-6 fill-primary" />
                            </Link>
                        </td>
                        <td class="px-4 py-3 capitalize">
                            <Link class="flex items-center pr-2" :href="`/my-applications/stall-rental-permits/${bsn.id}/edit`" tabindex="-1">
                                <icon name="cheveron-right" class="block w-6 h-6 fill-primary" />
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="stallRentals?.total === 0">
                        <td class="px-6 py-4 border-t text-center" colspan="7">No records found.</td>
                    </tr>
                </template>
            </BaseTable>
        </div>
        <div class="mt-6 flex justify-center">
            <pagination :links="stallRentals?.links" />
        </div>
    </AppLayout>
</template>


<script setup>
import {
    defineProps, watch, reactive
} from 'vue';
import {
    Link,
    router
} from '@inertiajs/vue3';
import { throttle, mapValues } from 'lodash'
import pickBy from 'lodash/pickBy'

import AppLayout from '@/Layouts/AppLayout.vue'
import BaseTable from '@/Shared/BaseTable.vue';
import Pagination from '@/Shared/Pagination.vue';
import Icon from '@/Shared/Icons.vue'
import { formatDate } from '@/data/helper';
import Badge from '@/Shared/Badge.vue';

const headers = [
    'Business Name',
    'Stall Name',
    'Category',
    'Desired Start Date',
    'Status',
    '',
    ''
];


const props = defineProps({
    stallRentals: Object,
    filters: Object
});

const form = reactive({ 
    search: props?.filters?.search,
});

watch(
  form,
  throttle(() => {
    router.get(`/admin/applications/stalls`, pickBy(form), { preserveState: true })
  }, 150),
  { deep: true }
);

const status = (stat) => {
    let permitStatus = {};
    switch (stat) {
        case 3:
            permitStatus = {
                color: 'orange',
                status: 'Expired'
            };
            break;
        case 3:
            permitStatus = {
                color: 'red',
                status: 'Rejected'
            };
            break;
        case 1:
            permitStatus = {
                color: 'green',
                status: 'Approved'
            };
            break;
        default:
            permitStatus = {
                  color: 'blue',
                status: 'Pending'
               
            };
    }
   
    return permitStatus;
};
</script>
