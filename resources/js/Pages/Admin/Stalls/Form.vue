<!-- Any Inertia page or normal Vue route -->
<template>
    <AppLayout title="Stalls">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
               Stalls
            </h2>
        </template>
         <h1 class="mb-8 text-3xl font-bold">
          <Link class="text-primary hover:text-secondary" :href="`/admin/system-setting/stalls`">Stalls</Link>
          <span class="text-secondary font-medium">/</span>
          {{checkIfEdit() ? `${props?.stall.name}` : 'Create New'}}
        </h1>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
            <form @submit.prevent="stores">
              <div class="flex flex-col md:flex-row gap-4 p-5">
                <div class="w-full md:w-1/2">
                    <InputLabel for="Name" value="Name" />
                    <TextInput
                        id="Name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form?.errors?.name" />
                </div>
                <div class="w-full md:w-1/2">
                    <InputLabel for="area_of_sqr_meter" value="Area of Square Meter" />
                    <TextInput
                        id="area_of_sqr_meter"
                        v-model="form.area_of_sqr_meter"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autocomplete="area_of_sqr_meter"
                    />
                    <InputError class="mt-2" :message="form?.errors?.area_of_sqr_meter" />
                </div>
              </div>
              <div class="flex flex-col md:flex-row gap-4 px-5">
                <div class="w-full md:w-1/2">
                  <InputLabel for="lang" value="Size - Latitude" />
                  <TextInput
                      id="lang"
                      v-model="form.lang"
                      type="number"
                      class="mt-1 block w-full"
                      required
                      placeholder = "Latitude"
                      autofocus
                      autocomplete="lang"
                  />
                  <InputError class="mt-2" :message="form?.errors?.lang" />
                </div>
                <div class="w-full md:w-1/2">
                  <InputLabel for="long" value="Size - Longitude" />
                  <TextInput
                      id="long"
                      v-model="form.long"
                      type="number"
                      class="mt-1 block w-full"
                      required
                      placeholder = "Longitude"
                      autofocus
                      autocomplete="long"
                  />
                  <InputError class="mt-2" :message="form?.errors?.long" />
                </div>
              </div>
              <div class="flex flex-col md:flex-row gap-4 p-5">
                <div class="w-full md:w-1/2">
                  <InputLabel for="stall_category_id" value="Category" class="mb-1 block w-full"/>
                  <SelectInput
                    v-model="form.stall_category_id"
                    id="stall_category_id"
                    required
                    autocomplete="stall_category_id"
                  >
                    <option
                        v-for="cat in stallsCategories"
                        :key="cat.id"
                        :value="cat.id"
                    >{{ cat.name }}</option> 
                  </SelectInput>
                  <InputError class="mt-2" :message="form?.errors?.stall_category_id" />
                </div>
                <div class="w-full md:w-1/2">
                  <InputLabel for="is_transient" value="For Transient / Volante" class="mb-1 block w-full"/>
                  <Radio
                    v-model="is_transient"
                    :options="transientOptions"
                    name="is_transient"
                  />
                  <InputError class="mt-2" :message="form?.errors?.is_transient" />
                </div>
              </div>
              <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
                <Link href="/admin/system-setting/stalls" class="text-red-600 hover:underline" tabindex="-1">Cancel</Link>
                <loading-button :loading="form.processing" class="bg-primary ml-auto" type="submit">Save</loading-button>
              </div>
          </form>
        </div>
        </div>
    </AppLayout>
</template>


<script setup>
import { defineProps, watch } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import { formatDateShort, isEditPage } from '@/data/helper';
import AppLayout from '@/Layouts/AppLayout.vue'
import TextInput from '@/Components/TextInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import { ROLE_ID } from '@/data/data';
import Radio from '@/Shared/Radio.vue';
const page = usePage();

const props = defineProps({
    stallsCategories: Object,
    stall: Object
});

const checkIfEdit = () => {
  return isEditPage(page.url);
};


const form = useForm({ 
  name: checkIfEdit() ? props?.stall?.name : '',
  area_of_sqr_meter: checkIfEdit() ? props?.stall?.area_of_sqr_meter : '',
  long: checkIfEdit() ? JSON.parse(props?.stall?.size)?.long : '',
  lang: checkIfEdit() ? JSON.parse(props?.stall?.size)?.lat : '',
  size: '',
  stall_category_id: checkIfEdit() ? props?.stall?.stall_category_id : '',
  is_transient: checkIfEdit() ? props?.stall?.is_transient : '',
});

const transientOptions = [
  { label: 'Yes', value: true },
  { label: 'No', value: false },
];

const getAreaOfSqrMeter = () => { 
    form.size = JSON.stringify({
        long: Number(form.long),
        lat: Number(form.lang)
    })
};


watch(
  () => [form.long, form.lang],
  () => {
    getAreaOfSqrMeter();
  }
);


const submit = () => {
    delete form.lang;
    delete form.long;
    form.post(route('admin.stalls.store'));
};

const update = () => {
  delete form.email;
  form.put(route('admin.stalls.update', props?.stall?.id));
};

const stores = () => {
  const edit = checkIfEdit();
  if(edit){
    return update();
  }

  return submit();
}
</script>

