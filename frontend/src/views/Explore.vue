<script setup>
//Improtē funkcijas un ikonas
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import axios from 'axios';
  import CheckDecagram from 'vue-material-design-icons/CheckDecagram.vue'
  import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
  //Definē mainīgos
  const users = ref([]);
  const token = localStorage.getItem('authToken')
  const router = useRouter();
  //Funkcijas, kas nostrādā skatam ielādējoties
  onMounted(() => {
    //Ja nav autentifikācijas talons, tiek novirzīts uz pieslēgšanās skatu
    if(!token){
        router.push({
            name: 'login'
        })
        return
    }
    //Pieprasījums, kas iegūst lietotāju datus
    axios.get('http://localhost:8000/api/users', {
        headers: {
        Authorization: `Bearer ${token}`
        }
    })
    .then(response => {
      users.value = response.data.data.users;
      
    })
    .catch(error => {
        console.error('Error fetching users:', error);
    });
  });
</script>
<template>
  <div class="container mx-auto p-8">
    <div class="w-full text-black text-[22px] font-extrabold p-4 flex">
        <router-link :to="'/'">
            <ArrowLeft fillColor="#000000" :size="28" class="rounded-full hover:bg-gray-300 transition duration-200 ease-in-out block cursor-pointer mr-2"/>
        </router-link>
        <h1 class="text-3xl font-bold mb-6">Explore Users</h1>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        <div v-for="user in users" :key="user.id" class="bg-white p-4 shadow rounded-lg min-w-[200px]">
          <div class="relative w-16 h-16 rounded-full overflow-hidden mx-auto">
            <img :src="user.profile.picture" class="object-cover w-full h-full" alt="User Profile">
          </div>
            <div class="flex items-center">
                <h2 class="text-xl font-semibold mr-2">{{ user.profile.nickname }}</h2>
                <CheckDecagram v-if="user.profile.verified" fillColor="#fca521" :size="18"/>
            </div>
            <p class="text-gray-600">@{{ user.name }}</p>
            <router-link :to="{ name: 'profile', params: { id: user.id } }" class="mt-4 block text-blue-500 hover:underline">
                View Profile
            </router-link>
        </div>
    </div>
  </div>
</template>
  
