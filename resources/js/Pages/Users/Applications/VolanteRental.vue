<!-- Any Inertia page or normal Vue route -->
<template>
    <AppLayout title="Volante">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Volante
            </h2>
        </template>
        <h1 class="mb-8 text-3xl font-bold text-primary">Volante Rentals</h1>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <FlashMessage v-if="$page.props.flash.success" :message="$page.props.flash.success" type="success" />
            <FlashMessage v-if="$page.props.flash.error" :message="$page.props.flash.error" type="error" />
            <div class="flex items-center justify-end mb-6 md:justify-self-center w-full">
                <Link type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                :href="`/my-rentals/market-volante/create`" class="bg-btn-gradient text-white font-bold uppercase px-5 py-2 rounded focus:outline-none shadow hover:bg-primary-700 transition-colors">Apply for New Permit</Link>
            </div>
            <BaseTable :headers="headers" :data="volanteRentals?.data">
                <template #row="{ data }">
                    <tr v-for="(bsn, i) in data" :key="volanteRentals.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 capitalize text-center">{{bsn?.permits?.permit_number ? `${bsn?.permits?.permit_number }`: '-'}}</td>
                        <td class="px-4 py-3 capitalize text-center">{{`${bsn.vendor?.first_name} ${bsn.vendor.last_name}`}}</td>
                        <td class="px-4 py-3 capitalize text-center">{{ bsn.name }}</td>
                        <td class="px-4 py-3 capitalize text-center">{{bsn?.start_date && bsn?.end_date ? `${formatDate(bsn.start_date)} - ${formatDate(bsn.end_date)}` : '-' }}</td>
                        <td class="px-4 py-3 capitalize text-center">
                            <Badge :color="bsn?.permits?.type === 'new' ? 'green' : 'blue'">
                                {{ bsn?.permits?.type }}
                             </Badge>
                        </td>
                        <td class="px-4 py-3 capitalize text-center">{{formatAmount(bsn?.quarterly_payment)}}</td>
                        <td class="px-4 py-3 capitalize text-center">{{formatDate(new Date(bsn?.next_payment_due))}}</td>
                        <td class="px-4 py-3 capitalize text-center">{{formatAmount(bsn?.penalty)}}</td>
                        <td class="px-4 py-3 capitalize text-center">
                            <p class="font-semibold" :class="bsn?.current_payment === 'Paid' ? 'text-green-600':'text-red-600'">{{bsn?.current_payment}}</p>
                        </td>
                        <td class="px-4 py-3 capitalize text-center">
                             <Badge :color="status(bsn.permits.status).color">
                                {{ status(bsn.permits.status).status }}
                             </Badge>
                        </td>
                        <td class="w-px border-t" v-if=" props?.filters?.role !== 'user'">
                            <Icons name="cheveron-right" @click="getDetails(bsn)" class="block w-6 h-6 fill-primary" />
                        </td>
                    </tr>
                    <tr v-if="volanteRentals?.total === 0">
                        <td class="px-6 py-4 border-t text-center" colspan="11">No records found.</td>
                    </tr>
                </template>
            </BaseTable>
        </div>
        <div class="mt-6 flex justify-center">
            <pagination :links="volanteRentals?.links" />
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
import FlashMessage from '@/Shared/FlashMessage.vue';

const headers = [
    'Rental No.',
    'Business Name',
    'Transient/Tent',
    'Location',
    'Duration',
    'Area of Square Meter',
    'Category',
    'Applicant',
    'Duration Period',
    'Status',
];


const props = defineProps({
    volanteRentals: Object,
    filters: Object
});

const form = reactive({ 
    search: props?.filters?.search,
});

watch(
  form,
  throttle(() => {
    router.get(`/my-applications/volante-rental-permit`, pickBy(form), { preserveState: true })
  }, 150),
  { deep: true }
);


function duration(num) {
    let duration = '';
    if (num === 1) {
        duration = 'Daily';
    } else if (num === 2) {
        duration = 'Weekly';       
    } else {
        duration = 'Event based'
    }
    return duration;
}

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
