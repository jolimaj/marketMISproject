<!-- Any Inertia page or normal Vue route -->
<template>
    <AppLayout title="Stalls">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stalls
            </h2>
        </template>
        <h1 class="mb-8 text-3xl font-bold text-primary">Stalls</h1>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
             <search-filter v-model="form.search" class="mr-4 w-full max-w-md" @reset="reset">
                <label class="block text-gray-700">Stall Category:</label>
                <select v-model="form.category" class="form-select mt-1 w-full">
                    <option :value="null">All</option>
                    <option
                        v-for="categories in stallsCategories"
                        :key="categories.id"
                        :value="categories.id"
                    >{{ categories.name }}</option> 
                </select>
            </search-filter>
            </div>
            <BaseTable :headers="headers" :data="stallRentals?.data">
                <template #row="{ data }">
                    <tr v-for="(bsn, i) in data" :key="stallRentals.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 capitalize">{{`${bsn.user.first_name} ${bsn.user.first_name}`}}</td>
                        <td class="px-4 py-3 capitalize">{{ bsn.name }}</td>
                        <td class="px-4 py-3 capitalize">{{ bsn.stalls.name }}</td>
                        <td class="px-4 py-3 capitalize">{{bsn?.start_date && bsn?.end_date ? `${formatDate(bsn.start_date)} - ${formatDate(bsn.end_date)}` : '-' }}</td>
                        <td class="px-4 py-3 capitalize">
                             <Badge :color="status(bsn.permits.status).color">
                                {{ status(bsn.permits.status).status }}
                             </Badge>
                        </td>
                        <td class="w-px border-t" v-if=" props?.filters?.role !== 'user'">
                            <Icons name="cheveron-right" @click="getDetails(bsn)" class="block w-6 h-6 fill-primary" />
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
        <Drawer v-model="showDrawer" :title="'Application Details'" :data="drawerData.value">
            <template #default>
            <div v-if="drawerData">
                 <div class="ml-auto w-full max-w-xl bg-white shadow-lg h-full overflow-y-auto">
                    <!-- Application Info Section -->
                    <div class="p-4 border rounded-xl mb-4 bg-gray-50 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <!-- Car Icon -->
                        <Icons name="stalls" class="block w-6 h-6 fill-primary" />
                        <div>
                        <p class="font-semibold text-primary">{{ `Stall Rental Application (${drawerData?.permits?.type})` }}</p>
                        <div class="flex items-center text-xs text-gray-500 space-x-2">
                            <!-- Status Badge -->
                            <Badge :color="status(drawerData?.permits.status).color">
                                {{ status(drawerData?.permits.status).status }}
                             </Badge>
                            <span>{{`• Applied ${formatDateShort(drawerData.permits.created_at)}`}}</span>
                        </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-2" v-if="$page.props.auth.user?.role_id === 2">
                        <button class="flex items-center px-3 py-1.5 text-white bg-red-600 rounded-lg hover:bg-red-700 text-sm" @click="rejectPermits">
                        <!-- Reject SVG -->
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 
                            011.414 0L10 8.586l4.293-4.293a1 1 0 
                            111.414 1.414L11.414 10l4.293 4.293a1 1 
                            01-1.414 1.414L10 11.414l-4.293 4.293a1 
                            1 0 01-1.414-1.414L8.586 10 4.293 
                            5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Reject
                        </button>
                        <form @submit.prevent="approveConfirm">
                            <button class="flex items-center px-3 py-1.5 text-white bg-green-600 rounded-lg hover:bg-green-700 text-sm">
                            <!-- Approve SVG -->
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 
                                010 1.414l-8 8a1 1 0 
                                01-1.414 0l-4-4a1 1 0 
                                011.414-1.414L8 12.586l7.293-7.293a1 1 
                                0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Approve
                            </button>
                        </form>
                    </div>
                    </div>

                    <!-- Applicant Information -->
                    <div class="p-4 border rounded-xl mb-5">
                        <h3 class="text-md font-semibold mb-3 text-primary">Applicant Information</h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center space-x-2">
                            <!-- User SVG -->
                            <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a5 5 0 100 
                                10 5 5 0 000-10zM2 18a8 8 0 
                                1116 0H2z" clip-rule="evenodd"/>
                            </svg>
                            <span>
                                <strong class="text-secondary">Full Name: </strong>
                                <span class="text-gray-600"> {{`${drawerData?.user?.first_name} ${drawerData?.user?.last_name}` }}</span>
                            </span>
                            </li>
                            <li class="flex items-center space-x-2">
                            <!-- Email SVG -->
                            <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.94 6.34A2 2 0 
                                014 6h12a2 2 0 
                                011.06.34L10 11 2.94 6.34z"/>
                                <path d="M18 8l-8 5-8-5v6a2 2 0 
                                002 2h12a2 2 0 002-2V8z"/>
                            </svg>
                            <span>
                                <strong class="text-secondary">Email Address: </strong>
                                <span class="text-gray-600"> {{drawerData?.user?.email}}</span>
                            </span>
                            </li>
                            <li class="flex items-center space-x-2">
                            <!-- Phone SVG -->
                            <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 
                                011-1h2a1 1 0 011 1v2a1 1 0 
                                01-1 1H4v2.586a1 1 0 
                                01-.293.707l-.707.707a1 1 0 
                                000 1.414l3 3a1 1 0 
                                001.414 0l.707-.707A1 1 0 
                                019 12.586V10h2a1 1 0 
                                011 1v2a1 1 0 01-1 1H9.414a1 1 
                                0 00-.707.293l-2 2A1 1 0 
                                006 17.414V18a1 1 0 01-1 1H3a1 
                                1 0 01-1-1V3z"/>
                            </svg>
                        <span>
                                <strong class="text-secondary">Phone Number: </strong>
                                <span class="text-gray-600"> {{drawerData?.user?.mobile}}</span>
                            </span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <!-- Calendar SVG -->
                                <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a6 6 0 
                                00-6 6c0 4.418 6 10 6 10s6-5.582 
                                6-10a6 6 0 00-6-6zm0 8a2 2 0 
                                110-4 2 2 0 010 4z" clip-rule="evenodd"/>
                                </svg>
                                <span>
                                    <strong class="text-secondary">Address: </strong>
                                    <span class="text-gray-600"> {{drawerData?.user?.address}}</span>
                                </span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <!-- Calendar SVG -->
                                <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a1 1 0 00-1 1v1a1 1 0 
                                    002 0V3a1 1 0 00-1-1zM6 5a2 2 0 
                                    114 0v1H6V5zm6 0a2 2 0 114 0v1h-4V5zM4 
                                    9a2 2 0 012-2h8a2 2 0 012 2v2H4V9zm-1 
                                    4h14a1 1 0 011 1v2a2 2 0 
                                    01-2 2H5a2 2 0 
                                    01-2-2v-2a1 1 0 
                                    011-1z"/>
                                </svg>
                                <span>
                                    <strong class="text-secondary">Birthday: </strong>
                                    <span class="text-gray-600"> {{formatDateShort(drawerData?.user?.birthday)}}</span>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <!-- Business Information -->
                    <div class="p-4 border rounded-xl mb-5">
                        <h3 class="text-md font-semibold mb-3 text-primary">Business Information</h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center space-x-2">
                            <!-- User SVG -->
                            <Icons name="business-names" class="block w-6 h-6 fill-secondary" />
                            <span>
                                <strong class="text-secondary">Business Name: </strong>
                                <span class="text-gray-600">{{ drawerData?.name }}</span>
                            </span>
                            </li>
                            <li class="flex items-center space-x-2">
                            <!-- Email SVG -->
                            <Icons name="stall-names" class="block w-6 h-6 fill-secondary" />
                            <span>
                                <strong class="text-secondary">Stall Name: </strong>
                                <span class="text-gray-600"> {{drawerData?.stalls?.name}}</span>
                            </span>
                            </li>
                            <li class="flex items-center space-x-2">
                            <!-- Phone SVG -->
                            <Icons name="business-category" class="block w-6 h-6 fill-secondary" />
                            <span>
                                <strong class="text-secondary">Category: </strong>
                                <span class="text-gray-600"> {{drawerData?.stalls?.stallsCategories?.name}}</span>
                            </span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a6 6 0 
                                00-6 6c0 4.418 6 10 6 10s6-5.582 
                                6-10a6 6 0 00-6-6zm0 8a2 2 0 
                                110-4 2 2 0 010 4z" clip-rule="evenodd"/>
                                </svg>
                                <span>
                                    <strong class="text-secondary">Stall Location </strong>
                                    <span class="text-gray-600"> {{ drawerData?.stalls?.location }}</span>
                                </span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <!-- Calendar SVG -->
                                <Icons name="business-size" class="block w-6 h-6 fill-secondary" />
                                <span>
                                    <strong class="text-secondary">Size: </strong>
                                    <span class="text-gray-600"> {{`Longitude: ${JSON.parse(drawerData.stalls?.size)?.long}, Latitude: ${JSON.parse(drawerData.stalls?.size)?.lat}`}}</span>
                                </span>
                            </li>
                             <li class="flex items-center space-x-2">
                                <!-- Calendar SVG -->
                                <Icons name="business-area" class="block w-6 h-6 fill-secondary" />
                                <span>
                                    <strong class="text-secondary">Area of Square Meter: </strong>
                                    <span class="text-gray-600"> {{drawerData?.stalls?.area_of_sqr_meter}}</span>
                                </span>
                            </li>
                        </ul>
                    </div>
                     <!-- Requirements Submitted -->
                    <div class="p-4 border rounded-xl mb-5">
                        <h3 class="text-md font-semibold mb-3 text-primary">Requirements Submitted</h3>
                        <ul class="space-y-2">
                                <li v-for="(doc, i) in drawerData.requirements" :key="i" class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg shadow-sm">
                                <div class="flex items-center space-x-2">
                                    <Icons name="requirements" class="block w-6 h-6 fill-primary" />
                                    <span class="text-gray-600">{{ doc.name }}</span>
                                </div>

                                <button class="flex items-center px-2 py-1 text-blue-600 hover:text-blue-800 text-sm">
                                    <a
                                    v-if="doc.url"
                                    :href="`${doc.url}/${doc.attachment}`"
                                    class="text-blue-600 ml-2 hover:underline"
                                    target="_blank"
                                >
                                    [View]
                                </a>
                                </button>
                                </li>
                            </ul>
                    </div>
                     <!-- Payments -->
                    <div class="p-4 border rounded-xl">
                        <h3 class="text-md font-semibold mb-3 text-primary">Payments</h3>
                         <ul class="space-y-2 text-sm">
                            <li class="flex items-center space-x-2">
                            <span>
                                <strong class="text-secondary">Days: </strong>
                                <span class="text-gray-600"> {{`${drawerData?.payments?.days}` }}</span>
                            </span>
                            </li>
                            <li class="flex items-center space-x-2">
                              <span>
                                <strong class="text-secondary">Months: </strong>
                                <span class="text-gray-600"> {{`${drawerData?.payments?.months}` }}</span>
                                </span>
                            </li>
                            <li class="flex items-center space-x-2">
                            <ul class="space-y-2">
                                <li v-for="(doc, i) in drawerData?.payments?.breakdown" :key="i" class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg shadow-sm">
                                <div class="flex items-center space-x-2">
                                   <span>
                                        <strong class="text-secondary">{{`• ${doc?.type}: `}} </strong>
                                        <span class="text-gray-600"> {{formatAmount(doc?.amount)}}</span>
                                    </span>
                                </div>
                                </li>
                            </ul>
                            </li>
                            <li class="flex items-center space-x-2">
                                <span>
                                    <strong class="text-secondary">Total: </strong>
                                    <span class="text-gray-600"> {{formatAmount(drawerData?.payments?.total)}}</span>
                                </span>
                            </li>
                        </ul>
                    </div>   
                </div>
                   
            </div>
            </template>
        </Drawer>
        <ConfirmationModal :show="isRejected" @close="closeRejection">
                <template #title>
                    Reject Request
                </template>

                <template #content>
                    <div class="space-y-4 p-4">
                        <!-- Question -->
                        <p class="text-gray-700 text-sm md:text-base">
                            Are you sure you would like to reject this request?  
                            Please provide a reason below:
                        </p>

                        <!-- Remarks field -->
                        <div>
                            <textarea
                                id="remarks"
                                v-model="form.remarks"
                                rows="3"
                                required
                                placeholder="Enter your reason for rejection..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                    focus:border-red-500 focus:ring-red-500 sm:text-sm resize-none"
                            ></textarea>
                            <InputError class="mt-2" :message="form?.errors?.remarks" />
                        </div>
                    </div>
                </template>

                <template #footer>
                    <div class="flex justify-end space-x-3 p-4">
                        <SecondaryButton @click="closeRejection">
                            Cancel
                        </SecondaryButton>
                    <form @submit.prevent="rejectConfirm">

                        <DangerButton
                            :disabled="!form.remarks"
                            class="disabled:opacity-50"
                        >
                            Reject
                        </DangerButton>
                    </form> 
                    </div>
                </template>
        </ConfirmationModal>
    </AppLayout>
</template>


<script setup>
import {
    defineProps, watch, reactive, ref
} from 'vue';
import {
    formatAmount,
    formatDate,
    formatDateShort
} from '@/data/helper';
import {
    router,
    useForm
} from '@inertiajs/vue3';
import { throttle, mapValues } from 'lodash'
import pickBy from 'lodash/pickBy'

import AppLayout from '@/Layouts/AppLayout.vue'
import BaseTable from '@/Shared/BaseTable.vue';
import Pagination from '@/Shared/Pagination.vue';
import SearchFilter from '@/Shared/SearchFilter.vue'
import Badge from '@/Shared/Badge.vue';
import Icons from '@/Shared/Icons.vue';
import Drawer from '@/Components/Drawer.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const headers = [
    'Applicants Name',
    'Business Name',
    'Stall Name',
    'Duration Date',
    'Status', ''];

const showDrawer = ref(false)
const drawerData = ref({})
const isRejected = ref(false)

const props = defineProps({
    stallsCategories: Object,
    stallRentals: Object,
    filters: Object,
    auth: Object,
});

const form = reactive({ 
    search: props?.filters?.search,
    category: props?.filters?.category,
});

const forms= useForm({ 
    remarks: '',
});

watch(
  form,
  throttle(() => {
    router.get(props.auth.user?.role_id === 1 ? '/admin/applications/stalls' : '/department/applications/stall-rental-permits', pickBy(form), { preserveState: true })
  }, 150),
  { deep: true }
);

function reset() {
    form.search = '';
    form.category = null;
    Object.assign(form, mapValues(form, () => null));
}

function getDetails(data) {
    drawerData.value = data;
    showDrawer.value = true;
}
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

const closeRejection = () => {
    isRejected.value = false;
};

const rejectPermits = () => {
    isRejected.value = true;
};

const approveConfirm = () => {
    delete forms.remarks;
    forms.put(route('department.applications.stalls.approve', drawerData?.value?.id));
};

const rejectConfirm = () => {
    delete forms.remarks;
    forms.put(route('department.applications.stalls.reject', drawerData?.value?.id));
};
</script>
