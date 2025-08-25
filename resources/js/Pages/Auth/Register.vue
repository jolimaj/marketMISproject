<script setup>
import { ref } from 'vue';
import { Link, useForm} from '@inertiajs/vue3';
import Logo from '@/Shared/Logo.vue';
import Background from '@/Shared/Background.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Footer from '@/Components/Footer.vue';
import Radio from '@/Shared/Radio.vue';
import DatePick from '@/Shared/DatePick.vue';

const selectedGender = ref(1);

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    role_id: 3,
    gender_id: selectedGender.value,
    sub_admin_type_id: null,
    user_type_id: null,
    department_id: null,
    address: '',
    mobile: '',
    birthday: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onSuccess: () => form.reset(
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'mobile',
            'address',
            'birthday',
            'gender_id',
            'sub_admin_type_id',
            'user_type_id',
            'department_id',
            'password', 'password_confirmation'
        )
    });
};


const genderOptions = [
  { label: 'Female', value: 1 },
  { label: 'Male', value: 2 },
  { label: 'Other', value: 3 },
];
</script>

<template>
  <Head title="MARKETIS" />
  <Background>
    <template #logo>
      <div class="flex flex-col sm:justify-center items-center justify-center mb-6">
        <Logo class="fill-white" style="width: 200px; height: 100px;" />
      </div>
    </template>
  
    <template #status>
      <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
      </div>
    </template>

    <template #forms>
      <form @submit.prevent="submit">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="w-full md:w-1/2">
            <InputLabel for="first_name" value="First Name" />
            <TextInput
                id="first_name"
                v-model="form.first_name"
                type="text"
                class="mt-1 block w-full"
                required
                autofocus
                autocomplete="first_name"
            />
            <InputError class="mt-2" :message="form.errors.first_name" />
          </div>
          <div class="w-full md:w-1/2">
            <InputLabel for="middle_name" value="Middle Name" />
            <TextInput
                id="middle_name"
                v-model="form.middle_name"
                type="text"
                class="mt-1 block w-full"
                required
                autofocus
                autocomplete="middle_name"
            />
            <InputError class="mt-2" :message="form.errors.middle_name" />
          </div>
        </div>

        <div class="mt-4 flex flex-col md:flex-row gap-4">
          <div class="w-full md:w-full">
            <InputLabel for="last_name" value="Last Name" />
            <TextInput
                id="last_name"
                v-model="form.last_name"
                type="text"
                class="mt-1 block w-full"
                required
                autofocus
                autocomplete="last_name"
            />
            <InputError class="mt-2" :message="form.errors.last_name" />
          </div>
          <div class="w-full md:w-full">
            <InputLabel for="birthday" value="Date of Birth" class="mb-1 block w-full"/>
             <DatePick
              v-model="form.birthday"
              id="birthday"
              required
              autofocus
              autocomplete="birthday"
            />
            <InputError class="mt-2" :message="form.errors.birthday" />
          </div>
        </div>

        <div class="mt-4">
          <InputLabel for="gender_id" value="Gender" />
          <div class="w-full w-full">
            <Radio
              v-model="selectedGender"
              :options="genderOptions"
              name="gender_id"
            />
          </div>
        </div>

        <div class="w-full mt-4">
          <InputLabel for="address" value="Address" />
          <textarea
              id="address"
              v-model="form.address"
              type="text"
              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
              required
              autocomplete="address"
          />
          <InputError class="mt-2" :message="form.errors.address" />
        </div>

        <div class="mt-4 flex flex-col md:flex-row gap-4">
          <div class="w-full md:w-full">
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1 block w-full"
                required
                autocomplete="email"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>
          <div class="w-full md:w-full">
            <InputLabel for="mobile" value="Mobile Number" />
            <TextInput
                id="mobile"
                v-model="form.mobile"
                type="number"
                class="mt-1 block w-full"
                required
                autocomplete="username"
            />
            <InputError class="mt-2" :message="form.errors.mobile" />
          </div>
        </div>

        <div class="mt-4 flex flex-col md:flex-row gap-4">
          <div class="w-full md:w-1/2">
            <InputLabel for="password" value="Password" />
            <TextInput
                id="password"
                v-model="form.password"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password" />
          </div>
          <div class="w-full md:w-1/2">
            <InputLabel for="password_confirmation" value="Confirm Password" />
            <TextInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password_confirmation" />
          </div>
        </div>

        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
          <InputLabel for="terms">
            <div class="flex items-center">
              <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

              <div class="ms-2">
                I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Privacy Policy</a>
              </div>
            </div>
            <InputError class="mt-2" :message="form.errors.terms" />
          </InputLabel>
        </div>
        <div class="block my-4">
          <button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full bg-btn-gradient text-white font-bold uppercase px-5 py-2 rounded focus:outline-none shadow hover:bg-primary-700 transition-colors">Sign Up</button>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-center items-center">
          <Link :href="route('login')" class="underline text-sm text-red-600 hover:text-red-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            Already registered?
          </Link>

        </div>
        
      </form>
    </template>
  </Background>
  <Footer />
</template>
