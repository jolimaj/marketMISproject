<template>
  <AppLayout title="Volante">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Volante</h2>
    </template>

    <h1 class="mb-8 text-3xl font-bold">
      <Link class="text-primary hover:text-secondary" :href="`/my-applications/volante-rental-permits`">Volante Rentals</Link>
      <span class="text-secondary font-medium">/</span>
      {{ checkIfEdit() ? `${props?.volanteRental?.name}` : 'Create New' }}
    </h1>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
        <div class="flex items-center px-8 py-4 bg-primary/20 border-t bg-primary/20">
          <loading-button
            :loading="form.processing"
            class="bg-btn-gradient ml-auto"
            type="submit"
            :disabled="stallRental?.status !== 1"
          >
            Renew
          </loading-button>
        </div>
        <!-- Step indicator -->
        <!-- Stepper -->
        <div class="flex items-center justify-between mt-6">
          <!-- Step 1 -->
          <div class="flex flex-col items-center text-center w-1/3">
            <div
              class="flex items-center justify-center w-12 h-12 rounded-full border-2"
              :class="step === 1 ? 'bg-primary text-white border-primary' : 'border-gray-300 text-gray-400'"
            >
              <!-- User/Business Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5.121 17.804A7.968 7.968 0 0112 15c1.933 0 3.683.69 5.121 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <span class="mt-2 text-sm" :class="step === 1 ? 'text-primary font-semibold' : 'text-gray-400'">Business Info</span>
          </div>

          <!-- Divider -->
          <div class="flex-1 border-t-2 mx-2" :class="step > 1 ? 'border-primary' : 'border-gray-200'"></div>

          <!-- Step 2 -->
          <div class="flex flex-col items-center text-center w-1/3">
            <div
              class="flex items-center justify-center w-12 h-12 rounded-full border-2"
              :class="step === 2 ? 'bg-primary text-white border-primary' : 'border-gray-300 text-gray-400'"
            >
              <!-- Document Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h10M7 11h10M7 15h6M5 5v14h14V5H5z" />
              </svg>
            </div>
            <span class="mt-2 text-sm" :class="step === 2 ? 'text-primary font-semibold' : 'text-gray-400'">Requirements</span>
          </div>

          <!-- Divider -->
          <div class="flex-1 border-t-2 mx-2" :class="step > 2 ? 'border-primary' : 'border-gray-200'"></div>

          <!-- Step 3 -->
          <div class="flex flex-col items-center text-center w-1/3">
            <div
              class="flex items-center justify-center w-12 h-12 rounded-full border-2"
              :class="step === 3 ? 'bg-primary text-white border-primary' : 'border-gray-300 text-gray-400'"
            >
              <!-- Payment Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 1.343-3 3h6c0-1.657-1.343-3-3-3zm9 3c0 4.418-4.03 8-9 8s-9-3.582-9-8V7h18v4z" />
              </svg>
            </div>
            <span class="mt-2 text-sm" :class="step === 3 ? 'text-primary font-semibold' : 'text-gray-400'">Payment</span>
          </div>
        </div>
        <form @submit.prevent="stores">
          <!-- Step 1: Input Details -->
          <div v-if="step === 1" class="p-6 space-y-6">
            <div class="flex flex-col md:flex-row gap-4">
              <div class="w-full">
                <InputLabel for="Business Name" value="Business Name" />
                <TextInput
                  id="business_name"
                  v-model="form.business_name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                  autocomplete="business_name"
                />
                <InputError class="mt-2" :message="form?.errors?.business_name" />
              </div>
              <div class="w-full">
                <InputLabel for="stall_id" value="Stall" class="mb-1 block w-full"/>
                <SelectInput
                  v-model="form.stall_id"
                  id="stall_id"
                  required
                  class="w-full"
                  autocomplete="stall_id"
                  :disabled="checkIfEdit()"
                >
                  <option
                    v-for="cat in stallTypes"
                    :key="cat.id"
                    :value="cat.id"
                    :disabled="cat.status === 1 || cat.status === 3"
                  >
                    {{ cat.status === 1 || cat.status === 3 
                      ? `${cat.name} (${cat.categories.description}) - OCCUPIED`
                      : `${cat.name} (${cat.categories.description})` }}
                  </option>
                </SelectInput>
                <InputError class="mt-2" :message="form?.errors?.stall_id" />
              </div>
            </div>

            <!-- Show stall info -->
            <div v-if="selectedStall" class="border p-4 rounded bg-gray-50">
              <h2 class="text-lg font-bold mb-2 text-secondary">Transient Details</h2>
              <p><strong>Name:</strong> {{ selectedStall.name }}</p>
              <p><strong>Size:</strong> {{ selectedStall.area_of_sqr_meter }}</p>
              <p><strong>Coordinates:</strong> {{ `Longitude-${JSON.parse(selectedStall.size).long}, Latitude-${JSON.parse(selectedStall.size).lat}` }}</p>
              <p><strong>Category:</strong> {{ selectedStall.categories.name }}</p>
              <p><strong>Description:</strong> {{ selectedStall.categories.description }}</p>

              <h3 class="mt-2 font-semibold text-secondary">Fees:</h3>
              <ul class="ml-4 list-disc">
                <li v-for="fee in selectedStall.categories.fee" :key="fee.id">
                  <strong>{{ fee.type }}:</strong> {{ feeDetails(fee) }}
                </li>
              </ul>
            </div>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-full">
                  <InputLabel for="Quantity" value="Quantity(kilo or styro)" />
                  <TextInput
                      id="quantity"
                      v-model="form.quantity"
                      type="number"
                      class="mt-1 block w-full"
                      required
                      autofocus
                      autocomplete="quantity"
                  />
                  <InputError class="mt-2" :message="form?.errors?.quantity" />
                </div>
                <div class="w-full md:w-full">
                 <InputLabel for="duration" value="Duration" class="mb-1 block w-full"/>
                  <SelectInput
                    v-model="form.duration"
                    id="duration"
                    required
                    class="w-full"
                    autocomplete="duration"
                  >
                    <option
                        v-for="cat in durationsOptions"
                        :key="cat.id"
                        :value="cat.id"
                    >{{ cat.name }}</option> 
                  </SelectInput>
                  <InputError class="mt-2" :message="form?.errors?.duration" />
                </div>
              </div>
              <div class="mt-4 flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-full">
                  <InputLabel for="started_date" value="Start Date" />
                  <DatePick
                      id="started_date"
                      v-model="form.started_date"
                      class="mt-1 block w-full"
                      required
                      autofocus
                      autocomplete="started_date"
                    />
                  <InputError class="mt-2" :message="form.errors.started_date" />
                </div>
                <div class="w-full md:w-full">
                  <InputLabel for="end_date" value="End Date" />
                   <DatePick
                      id="end_date"
                      v-model="form.end_date"
                      class="mt-1 block w-full"
                      required
                      autofocus
                      autocomplete="end_date"
                    />
                  <InputError class="mt-2" :message="form.errors.end_date" />
                </div>
              </div>
          </div>

          <!-- Step 2: Requirements -->
          <div v-if="step === 2" class="p-6 space-y-6">
            <DocumentUpload
              :requiredDocs="checkIfEdit() ? requirements?.volante_new : requirements?.volante_renew"
              v-model:files="localFiles"
            />
            <InputError class="mt-2" :message="form.errors.requirements" />
          </div>

          <!-- Step 3: Payments -->
          <div v-if="step === 3" class="p-6 space-y-6">
            <h2 class="text-lg font-bold text-secondary mb-2">Payments</h2>
            <p class="text-gray-600">Please complete your payment below:</p>
            <iframe 
              src="https://www.lbp-eservices.com/egps/portal/Fields.jsp" 
              class="w-full h-96 border rounded-md"
            ></iframe>
            <div class="flex flex-col md:flex-row gap-4 mt-5">
              <div class="w-full">
              <PaymentSummaryCard :details="step === 3 ? paymentDetails : null" />
              </div>
            </div>
            <div class="flex flex-col md:flex-row gap-4 mt-5">
              <div class="w-full">
                <InputLabel for="reference_number" value="Reference Number" />
                <TextInput
                  id="reference_number"
                  v-model="form.reference_number"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                  autocomplete="reference_number"
                />
                <InputError class="mt-2" :message="form?.errors?.reference_number" />
              </div>
              <div class="w-full">
                <InputLabel for="receipt" value="Receipt" class="mb-1 block w-full"/>
                <SingleFileUpload
                  v-model="form.receipt"
                  label="Upload Official Receipt"
                  accept=".jpg,.jpeg,.png,.pdf"
                  class="mt-1 block w-full"
                  :error="form?.errors?.receipt"
                />
                <InputError class="mt-2" :message="form?.errors?.receipt" />
              </div>
            </div>
          </div>

          <!-- Step navigation -->
          <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
            <Link href="/my-rentals/market-volante" class="text-red-600 hover:underline" tabindex="-1">Cancel</Link>
            
            <div class="ml-auto flex gap-2">
              <button
                v-if="step > 1"
                type="button"
                class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300"
                @click="step--"
              >
                Back
              </button>

              <button
                v-if="step < 3"
                type="button"
                class="px-4 py-2 rounded-md bg-primary text-white hover:bg-secondary"
                @click="nextButton"
                :disable="volanteRental?.status === 1"
              >
                Next
              </button>

              <loading-button
                v-if="step === 3"
                :loading="form.processing"
                class="bg-primary ml-auto"
                type="submit"
              >
                Save
              </loading-button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { defineProps, computed, ref } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import { formatAmount, formatDateShort, isEditPage } from '@/data/helper';
import AppLayout from '@/Layouts/AppLayout.vue'
import TextInput from '@/Components/TextInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import DocumentUpload from '@/Shared/DocumentUpload.vue';
import DatePick from '@/Shared/DatePick.vue';
import SingleFileUpload from '@/Shared/SingleFileUpload.vue';
import PaymentSummaryCard from '@/Shared/PaymentSummaryCard.vue';

const page = usePage();
const props = defineProps({
  volanteRental: Object,
  stallTypes: Object,
  requirements: Object,
  paymentDetails: Object,
  total_payments: Object,
});

const step = ref(1);

const checkIfEdit = () => {
  return isEditPage(page.url);
};

const durationsOptions  = [
  { id: 1, name: 'Daily '},
  { id: 2, name: 'Weekly '},
  { id: 3, name: 'Event-based'},
]
const localFiles = ref([]); 

const form = useForm({ 
  business_name: checkIfEdit() ? props?.volanteRental?.name : '',
  location: checkIfEdit() ? props?.volanteRental?.location : '',
  duration: checkIfEdit() ? props?.volanteRental?.duration : [],
  started_date: checkIfEdit() ? formatDateShort(props?.volanteRental?.start_date) : '',
  end_date: checkIfEdit() ? formatDateShort(props?.volanteRental?.end_date): '',
  stall_id: checkIfEdit() ? props?.volanteRental?.stalls?.id : '',
  quantity: checkIfEdit() ? props?.volanteRental?.quantity : '',
  requirements: [],
  receipt: null,
  reference_number: checkIfEdit() ? props?.volanteRental?.paymentRecord?.reference_number : '',
  amount: 0,
  step,
});

const selectedStall = computed(() => props?.stallTypes?.find(s => s.id === Number(form.stall_id)))

const submit = () => {
  form.requirements = checkIfEdit() 
    ? props?.volanteRental?.requirements.map(f => ({
        requirement_checklist_id: f.checklist_id, 
        attachment: f.attachment                        
      })) 
    : localFiles.value.map(f => ({
        requirement_checklist_id: f.documentType, 
        attachment: f.file                        
      }));
  form.amount = props?.paymentDetails?.total;
  form.location = selectedStall?.value?.location;
  form.post(route("applications.volantes.store"), {
    onSuccess: () => {
      form.reset();
      localFiles.value = [] 
    }
  })
};

const update = () => {
  form.put(route('applications.volantes.update', props?.volanteRental?.id));
};


const nextButton = () => {
  if (step.value === 1) {
    console.log(checkIfEdit());
    if(checkIfEdit()){
      step.value++;
    } else {
      form.post(route("applications.volantes.store"), {
        preserveState: true,
        onSuccess: () => {
          step.value++;
        }
      })
    }
  } 
  else if (step.value === 2) {
    if(checkIfEdit()){
      step.value++;
    }
    else {
      if (localFiles.value.some(file => file.status === 'uploading')) {
       form.errors.requirements = "Please wait until all files are uploaded."
      } 
      else if (localFiles.value.length === 0) {
        form.errors.requirements = "The requirements are required."
      }
      else {
        step.value++;
      }
    }

  }
  else if (step.value === 3) {
    if(checkIfEdit()){
      step.value++;
    }
    else {
      if (localFiles.value.some(file => file.status === 'uploading')) {
       form.errors.requirements = "Please wait until all files are uploaded."
      } 
      else if (localFiles.value.length === 0) {
        form.errors.requirements = "The requirements are required."
      }
      else {
        step.value++;
      }
    }

  }
  form.step = step.value;
};

const stores = () => {
  if(step.value === 3){
    return checkIfEdit() ? update() : submit();
  }
}

const feeDetails = (fee) => {
  if(fee?.is_monthly) {
    return `${formatAmount(fee.amount)} per month`;
  }
  if(fee?.is_daily) {
    return `${formatAmount(fee.amount)} per day`;
  }
  return `${formatAmount(fee.amount)}`
}
</script>